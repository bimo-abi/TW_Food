<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pesanan;
use App\Models\RiwayatAktivitasTransaksi;
use App\Services\ProduksiPreOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MitraPesananController extends Controller
{
    private function pastikanMitra(Request $request): void
    {
        abort_unless($request->user() && $request->user()->peran === 'mitra' && $request->user()->status_aktif, 403, 'Akses khusus Mitra aktif.');
    }

    public function index(Request $request)
    {
        $this->pastikanMitra($request);

        $query = Pesanan::query()->with(['pengguna:id_pengguna,nama,nomor_telepon', 'detailPesanan', 'pembayaran'])
            ->latest('created_at');

        foreach (['status_pesanan', 'status_pembayaran', 'jenis_pemenuhan', 'jenis_pesanan'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        return response()->json(['message' => 'Daftar pesanan operasional berhasil diambil.', 'data' => $query->paginate(min((int) $request->query('per_page', 20), 50))]);
    }

    public function show(Request $request, int $id_pesanan)
    {
        $this->pastikanMitra($request);

        $pesanan = Pesanan::with([
            'pengguna:id_pengguna,nama,email,nomor_telepon,jenis_pelanggan',
            'detailPesanan.produksiPreOrder',
            'detailPesanan.reservasiStok',
            'pembayaran',
            'retur.detailRetur',
            'retur.buktiRetur',
            'reservasiStok',
            'refund',
            'riwayatAktivitas',
            'outlet',
        ])->findOrFail($id_pesanan);

        return response()->json(['message' => 'Detail pesanan operasional berhasil diambil.', 'data' => $pesanan]);
    }

    /** Perubahan status operasional mengikuti transisi PRD dan mengunci record pesanan. */
    public function updateStatus(Request $request, int $id_pesanan, ProduksiPreOrderService $produksiPreOrderService)
    {
        $this->pastikanMitra($request);
        $data = $request->validate([
            'status_pesanan' => ['required', 'in:siap_diambil,siap_dikirim,dalam_pengiriman,diterima'],
        ]);
        $aktor = $request->user();

        $pesanan = DB::transaction(function () use ($id_pesanan, $data, $aktor, $produksiPreOrderService) {
            $pesanan = Pesanan::where('id_pesanan', $id_pesanan)->lockForUpdate()->firstOrFail();
            $lama = $pesanan->status_pesanan;
            $baru = $data['status_pesanan'];
            $alur = [
                'sedang_diproses' => ['siap_diambil', 'siap_dikirim'],
                'siap_diambil' => ['diterima'],
                'siap_dikirim' => ['dalam_pengiriman'],
                'dalam_pengiriman' => ['diterima'],
            ];

            if (!in_array($baru, $alur[$lama] ?? [], true)) {
                throw ValidationException::withMessages(['status_pesanan' => ['Transisi status tidak sesuai alur pesanan.']]);
            }
            if ($baru === 'siap_diambil' && $pesanan->jenis_pesanan !== 'pickup') {
                throw ValidationException::withMessages(['status_pesanan' => ['Status siap_diambil hanya untuk pickup.']]);
            }
            if (in_array($baru, ['siap_dikirim', 'dalam_pengiriman'], true) && $pesanan->jenis_pesanan !== 'delivery') {
                throw ValidationException::withMessages(['status_pesanan' => ['Status pengiriman hanya untuk delivery.']]);
            }
            if (in_array($baru, ['dalam_pengiriman', 'diterima'], true) && $pesanan->status_pembayaran !== 'lunas') {
                throw ValidationException::withMessages(['status_pesanan' => ['Pesanan wajib lunas sebelum diserahkan kepada kurir atau pelanggan.']]);
            }
            if ($baru === 'dalam_pengiriman') {
                if ($pesanan->kurir === 'gosend' && empty($pesanan->tautan_pelacakan)) {
                    throw ValidationException::withMessages(['tautan_pelacakan' => ['Tautan tracking GoSend wajib tersedia sebelum pengiriman.']]);
                }
                if ($pesanan->kurir === 'jnt' && empty($pesanan->nomor_resi)) {
                    throw ValidationException::withMessages(['nomor_resi' => ['Nomor resi J&T wajib tersedia sebelum pengiriman.']]);
                }
                if (!in_array($pesanan->kurir, ['gosend', 'jnt'], true)) {
                    throw ValidationException::withMessages(['kurir' => ['Kurir pesanan belum ditentukan.']]);
                }
            }

            if (in_array($baru, ['siap_diambil', 'siap_dikirim'], true)) {
                $produksiPreOrderService->selesaikanUntukPesanan($pesanan, $aktor, 'Flutter Mitra');
            }

            $pesanan->status_pesanan = $baru;
            if ($baru === 'diterima') {
                $pesanan->diterima_pada = $pesanan->diterima_pada ?? now();
            }
            $pesanan->save();

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'status_pesanan_diperbarui',
                'status_sebelum' => $lama,
                'status_sesudah' => $baru,
                'deskripsi' => 'Status pesanan diperbarui melalui Flutter Mitra.',
                'created_at' => now(),
            ]);

            Notifikasi::create([
                'id_pengguna' => $pesanan->id_pengguna,
                'judul' => 'Pembaruan status pesanan',
                'pesan' => 'Pesanan ' . $pesanan->nomor_pesanan . ' berubah menjadi ' . $baru . '.',
                'jenis' => 'pesanan',
                'jenis_referensi' => 'pesanan',
                'id_referensi' => $pesanan->id_pesanan,
                'sudah_dibaca' => false,
                'created_at' => now(),
            ]);

            return $pesanan->fresh();
        });

        return response()->json(['message' => 'Status pesanan berhasil diperbarui.', 'data' => $pesanan]);
    }

    /** Simpan data tracking ketika pesanan sudah siap dikirim. */
    public function updatePengiriman(Request $request, int $id_pesanan)
    {
        $this->pastikanMitra($request);
        $data = $request->validate([
            'kurir' => ['required', 'in:gosend,jnt'],
            'nomor_resi' => ['nullable', 'string', 'max:100'],
            'tautan_pelacakan' => ['nullable', 'url', 'max:255'],
        ]);

        $pesanan = DB::transaction(function () use ($id_pesanan, $data, $request) {
            $pesanan = Pesanan::where('id_pesanan', $id_pesanan)->lockForUpdate()->firstOrFail();
            if ($pesanan->jenis_pesanan !== 'delivery' || $pesanan->status_pesanan !== 'siap_dikirim') {
                throw ValidationException::withMessages(['pengiriman' => ['Tracking hanya dapat diperbarui untuk delivery berstatus siap_dikirim.']]);
            }
            if ($data['kurir'] === 'gosend' && empty($data['tautan_pelacakan'])) {
                throw ValidationException::withMessages(['tautan_pelacakan' => ['Tautan tracking GoSend wajib diisi.']]);
            }
            if ($data['kurir'] === 'jnt' && empty($data['nomor_resi'])) {
                throw ValidationException::withMessages(['nomor_resi' => ['Nomor resi J&T wajib diisi.']]);
            }

            $pesanan->kurir = $data['kurir'];
            $pesanan->nomor_resi = $data['kurir'] === 'jnt' ? $data['nomor_resi'] : null;
            $pesanan->tautan_pelacakan = $data['kurir'] === 'gosend' ? $data['tautan_pelacakan'] : null;
            $pesanan->save();

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $request->user()->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'informasi_pengiriman_diperbarui',
                'status_sebelum' => null,
                'status_sesudah' => 'tersimpan',
                'deskripsi' => 'Informasi tracking pengiriman diperbarui melalui Flutter Mitra.',
                'created_at' => now(),
            ]);

            Notifikasi::create([
                'id_pengguna' => $pesanan->id_pengguna,
                'judul' => 'Informasi pengiriman tersedia',
                'pesan' => 'Informasi pengiriman pesanan ' . $pesanan->nomor_pesanan . ' telah diperbarui.',
                'jenis' => 'pengiriman',
                'jenis_referensi' => 'pesanan',
                'id_referensi' => $pesanan->id_pesanan,
                'sudah_dibaca' => false,
                'created_at' => now(),
            ]);

            return $pesanan->fresh();
        });

        return response()->json(['message' => 'Informasi pengiriman berhasil diperbarui.', 'data' => $pesanan]);
    }
}
