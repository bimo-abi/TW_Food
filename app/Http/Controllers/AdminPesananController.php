<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Refund;
use App\Models\ReservasiStok;
use App\Services\TransaksiIntegrasiService;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction as MidtransTransaction;
use Throwable;

class AdminPesananController extends Controller
{
    public function index()
    {
        $pesanan = Pesanan::with('pengguna')
            ->latest('created_at')
            ->get();

        return view('admin.pesanan.index', compact('pesanan'));
    }

    public function show($id)
    {
        $pesanan = Pesanan::with([
            'pengguna',
            'alamat',
            'outlet',
            'detailPesanan.varian.produk',
            'pembayaran',
            'retur',
            'refund',
            'reservasiStok',
            'riwayatAktivitas',
        ])->findOrFail($id);

        return view('admin.pesanan.show', compact('pesanan'));
    }

    /**
     * Memperbarui status pesanan sesuai tujuh status resmi PRD v1.6.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'status_pesanan' => [
                'required',
                'in:menunggu_pembayaran,sedang_diproses,siap_diambil,siap_dikirim,dalam_pengiriman,diterima,dibatalkan',
            ],
            'alasan_pembatalan' => [
                'required_if:status_pesanan,dibatalkan',
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $aktor = $request->user();
        $statusBaru = $data['status_pesanan'];

        try {
            $hasil = DB::transaction(function () use ($id, $data, $aktor, $statusBaru) {
                $pesanan = Pesanan::where('id_pesanan', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $statusLama = $pesanan->status_pesanan;

                if ($statusLama === $statusBaru) {
                    return ['message' => 'Pesanan sudah berada pada status tersebut.'];
                }

                if (in_array($statusLama, ['diterima', 'dibatalkan'], true)) {
                    throw new \DomainException('Pesanan yang sudah diterima atau dibatalkan tidak dapat diubah statusnya.');
                }

                if ($statusBaru === 'dibatalkan') {
                    $this->batalkanPesananOperasional(
                        $pesanan,
                        $aktor,
                        $data['alasan_pembatalan']
                    );

                    return ['message' => 'Pesanan dibatalkan. Refund untuk pembayaran yang telah diterima dicatat untuk diproses dan diverifikasi.'];
                }

                $alur = [
                    'menunggu_pembayaran' => [],
                    'sedang_diproses' => ['siap_diambil', 'siap_dikirim'],
                    'siap_diambil' => ['diterima'],
                    'siap_dikirim' => ['dalam_pengiriman'],
                    'dalam_pengiriman' => ['diterima'],
                    'diterima' => [],
                    'dibatalkan' => [],
                ];

                if (!in_array($statusBaru, $alur[$statusLama] ?? [], true)) {
                    throw new \DomainException('Perubahan status tidak sesuai alur pesanan PRD v1.6.');
                }

                if ($statusBaru === 'siap_diambil' && $pesanan->jenis_pesanan !== 'pickup') {
                    throw new \DomainException('Status siap_diambil hanya berlaku untuk pesanan pickup.');
                }

                if (in_array($statusBaru, ['siap_dikirim', 'dalam_pengiriman'], true)
                    && $pesanan->jenis_pesanan !== 'delivery') {
                    throw new \DomainException('Status pengiriman hanya berlaku untuk pesanan delivery.');
                }

                // Barang tidak boleh diserahkan kepada pelanggan atau kurir sebelum lunas.
                if (in_array($statusBaru, ['diterima', 'dalam_pengiriman'], true)
                    && $pesanan->status_pembayaran !== 'lunas') {
                    throw new \DomainException('Pesanan harus lunas dan pembayaran terverifikasi sebelum diserahkan kepada pelanggan atau kurir.');
                }

                if ($statusBaru === 'dalam_pengiriman') {
                    $this->validasiInformasiPengiriman($pesanan);
                }

                $pesanan->status_pesanan = $statusBaru;

                if ($statusBaru === 'diterima') {
                    $pesanan->diterima_pada = $pesanan->diterima_pada ?? now();
                }

                $pesanan->save();

                $this->catatAktivitas(
                    $pesanan,
                    null,
                    $aktor,
                    'status_pesanan_diperbarui',
                    $statusLama,
                    $statusBaru,
                    'Status pesanan diperbarui oleh Admin/Mitra.'
                );

                $this->kirimNotifikasi(
                    $pesanan,
                    'Pembaruan status pesanan',
                    'Status pesanan ' . $pesanan->nomor_pesanan . ' berubah menjadi ' . $statusBaru . '.',
                    'pesanan'
                );

                return ['message' => 'Status pesanan berhasil diperbarui.'];
            });

            return redirect()
                ->route('admin.pesanan.show', $id)
                ->with('success', $hasil['message']);
        } catch (\DomainException $e) {
            return back()->withErrors(['status_pesanan' => $e->getMessage()])->withInput();
        } catch (Throwable $e) {
            Log::error('Gagal memperbarui status pesanan.', [
                'id_pesanan' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'status_pesanan' => 'Status pesanan gagal diperbarui. Periksa log aplikasi untuk detail teknis.',
            ])->withInput();
        }
    }

    /**
     * Menyimpan nomor resi atau tautan tracking sesuai kurir.
     */
    public function updatePengiriman(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        if ($pesanan->jenis_pesanan !== 'delivery') {
            return back()->withErrors([
                'pengiriman' => 'Informasi pengiriman hanya tersedia untuk pesanan delivery.',
            ]);
        }

        if ($pesanan->status_pesanan !== 'siap_dikirim') {
            return back()->withErrors([
                'pengiriman' => 'Informasi tracking hanya dapat disimpan ketika pesanan berstatus siap_dikirim.',
            ]);
        }

        $data = $request->validate([
            'nomor_resi' => ['nullable', 'string', 'max:100'],
            'tautan_pelacakan' => ['nullable', 'url', 'max:255'],
        ]);

        if ($pesanan->kurir === 'gosend' && empty($data['tautan_pelacakan'])) {
            return back()->withErrors([
                'tautan_pelacakan' => 'Tautan tracking GoSend wajib diisi.',
            ])->withInput();
        }

        if ($pesanan->kurir === 'jnt' && empty($data['nomor_resi'])) {
            return back()->withErrors([
                'nomor_resi' => 'Nomor resi J&T wajib diisi.',
            ])->withInput();
        }

        if (!in_array($pesanan->kurir, ['gosend', 'jnt'], true)) {
            return back()->withErrors([
                'pengiriman' => 'Kurir pesanan belum ditentukan dengan benar.',
            ]);
        }

        $pesanan->nomor_resi = $pesanan->kurir === 'jnt' ? $data['nomor_resi'] : null;
        $pesanan->tautan_pelacakan = $pesanan->kurir === 'gosend' ? $data['tautan_pelacakan'] : null;
        $pesanan->save();

        $this->catatAktivitas(
            $pesanan,
            null,
            $request->user(),
            'informasi_pengiriman_diperbarui',
            null,
            'tersimpan',
            'Informasi pengiriman ' . strtoupper($pesanan->kurir) . ' diperbarui.'
        );

        return redirect()
            ->route('admin.pesanan.show', $pesanan->id_pesanan)
            ->with('success', 'Informasi pengiriman berhasil disimpan.');
    }

    /**
     * Pembatalan operasional: batalkan transaksi Midtrans yang masih pending,
     * lalu buat catatan refund 100% untuk setiap transaksi yang sudah paid.
     */
    private function batalkanPesananOperasional(Pesanan $pesanan, $aktor, string $alasan): void
    {
        if ($pesanan->status_pesanan === 'diterima') {
            throw new \DomainException('Pesanan yang sudah diterima harus menggunakan proses retur, bukan pembatalan operasional.');
        }

        $pembayaran = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
            ->lockForUpdate()
            ->get();

        $pending = $pembayaran->where('status_pembayaran', 'pending');

        if ($pending->isNotEmpty()) {
            $serverKey = config('services.midtrans.server_key');
            if (!$serverKey) {
                throw new \DomainException('Server key Midtrans belum dikonfigurasi. Pembatalan transaksi pending tidak dapat diverifikasi.');
            }

            Config::$serverKey = $serverKey;
            Config::$isProduction = (bool) config('services.midtrans.is_production', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            foreach ($pending as $transaksi) {
                if ($transaksi->penyedia_pembayaran !== 'Midtrans' || !$transaksi->id_transaksi) {
                    throw new \DomainException('Transaksi pending non-Midtrans harus diverifikasi melalui penyedia pembayarannya sebelum pesanan dibatalkan.');
                }

                try {
                    // Permintaan cancel dapat gagal bila transaksi sudah dibatalkan;
                    // status API tetap diperiksa sebagai sumber kebenaran.
                    try {
                        MidtransTransaction::cancel($transaksi->id_transaksi);
                    } catch (Throwable $cancelError) {
                        Log::info('Permintaan cancel Midtrans memberikan respons non-final; status akan diverifikasi.', [
                            'id_transaksi' => $transaksi->id_transaksi,
                            'error' => $cancelError->getMessage(),
                        ]);
                    }

                    $hasilGateway = (array) MidtransTransaction::status($transaksi->id_transaksi);
                } catch (Throwable $e) {
                    Log::warning('Pembatalan transaksi pending Midtrans belum dapat diverifikasi.', [
                        'id_transaksi' => $transaksi->id_transaksi,
                        'error' => $e->getMessage(),
                    ]);
                    throw new \DomainException('Pembatalan transaksi pending belum dapat diverifikasi oleh Midtrans. Pesanan belum dibatalkan.');
                }

                $statusGateway = $hasilGateway['transaction_status'] ?? null;
                $grossGateway = number_format((float) ($hasilGateway['gross_amount'] ?? 0), 2, '.', '');
                $grossLokal = number_format((float) $transaksi->jumlah_pembayaran, 2, '.', '');
                $orderIdGateway = (string) ($hasilGateway['order_id'] ?? '');

                if (
                    $orderIdGateway !== (string) $transaksi->id_transaksi ||
                    $grossGateway !== $grossLokal ||
                    !in_array($statusGateway, ['cancel', 'expire', 'deny'], true)
                ) {
                    throw new \DomainException('Midtrans belum mengonfirmasi pembatalan transaksi. Pesanan belum dibatalkan.');
                }

                $statusSebelum = $transaksi->status_pembayaran;
                $transaksi->status_pembayaran = $statusGateway === 'expire' ? 'expired' : 'failed';
                $transaksi->respons_gateway = array_merge(
                    (array) $transaksi->respons_gateway,
                    ['hasil_pembatalan_operasional' => $hasilGateway]
                );
                $transaksi->save();

                $this->catatAktivitas(
                    $pesanan,
                    $transaksi,
                    $aktor,
                    'transaksi_pending_dibatalkan',
                    $statusSebelum,
                    $transaksi->status_pembayaran,
                    'Transaksi pending dibatalkan dan diverifikasi melalui Midtrans.'
                );
            }
        }

        $statusLama = $pesanan->status_pesanan;
        $pesanan->status_pesanan = 'dibatalkan';

        // Jika tidak ada dana yang pernah diterima, status pembayaran ikut dibatalkan.
        // Jika ada dana paid, status tetap sampai refund benar-benar terverifikasi.
        $masihAdaPembayaranBerhasil = Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
            ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->where('status_pembayaran', 'paid')
            ->exists();

        if (!$masihAdaPembayaranBerhasil) {
            $pesanan->status_pembayaran = 'dibatalkan';
        }

        $pesanan->save();

        // Lepaskan hanya reservasi yang masih aktif; reservasi yang telah dikonversi tidak disentuh.
        ReservasiStok::where('id_pesanan', $pesanan->id_pesanan)
            ->where('status_reservasi', 'aktif')
            ->update([
                'status_reservasi' => 'dilepas',
                'dilepas_pada' => now(),
                'alasan_pelepasan' => 'Pesanan dibatalkan karena kendala operasional.',
                'updated_at' => now(),
            ]);

        $totalRefundDiajukan = 0;

        foreach ($pembayaran->where('status_pembayaran', 'paid') as $transaksiPaid) {
            // Satu pembayaran hanya boleh memiliki satu refund pembatalan operasional.
            // Refund gagal/perlu diproses ulang harus memakai proses retry, bukan membuat refund baru.
            $refundSudahAda = Refund::where('id_pembayaran', $transaksiPaid->id_pembayaran)
                ->where('jenis_refund', 'pembatalan_operasional')
                ->exists();

            if ($refundSudahAda) {
                continue;
            }

            $nominalRefund = (int) $transaksiPaid->jumlah_pembayaran;
            if ($nominalRefund < 1) {
                continue;
            }

            $refund = Refund::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => $transaksiPaid->id_pembayaran,
                'jenis_refund' => 'pembatalan_operasional',
                'nominal_refund' => $nominalRefund,
                'status_refund' => 'menunggu_diproses',
                'alasan' => $alasan,
                'id_pengguna_penyetuju' => $aktor?->id_pengguna,
                'disetujui_pada' => now(),
            ]);

            // Panggilan gateway dijalankan setelah transaksi pembatalan commit.
            // Catatan refund tetap tersimpan walaupun gateway sedang tidak tersedia.
            DB::afterCommit(function () use ($refund) {
                app(TransaksiIntegrasiService::class)->prosesRefund($refund->id_refund);
            });

            $totalRefundDiajukan += $nominalRefund;
        }

        $this->catatAktivitas(
            $pesanan,
            null,
            $aktor,
            'pesanan_dibatalkan_operasional',
            $statusLama,
            'dibatalkan',
            'Pesanan dibatalkan karena kendala operasional. Alasan: ' . $alasan .
                '. Total refund yang dicatat untuk diproses: Rp' . number_format($totalRefundDiajukan, 0, ',', '.') . '.'
        );

        $pesan = $totalRefundDiajukan > 0
            ? 'Pesanan ' . $pesanan->nomor_pesanan . ' dibatalkan karena kendala operasional. Refund sebesar Rp' . number_format($totalRefundDiajukan, 0, ',', '.') . ' sedang menunggu pemrosesan dan verifikasi.'
            : 'Pesanan ' . $pesanan->nomor_pesanan . ' dibatalkan karena kendala operasional.';

        $this->kirimNotifikasi($pesanan, 'Pembatalan pesanan', $pesan, 'pesanan');
    }

    private function validasiInformasiPengiriman(Pesanan $pesanan): void
    {
        if ($pesanan->kurir === 'gosend' && empty($pesanan->tautan_pelacakan)) {
            throw new \DomainException('Tautan tracking GoSend wajib diisi sebelum pesanan dikirim.');
        }

        if ($pesanan->kurir === 'jnt' && empty($pesanan->nomor_resi)) {
            throw new \DomainException('Nomor resi J&T wajib diisi sebelum pesanan dikirim.');
        }

        if (!in_array($pesanan->kurir, ['gosend', 'jnt'], true)) {
            throw new \DomainException('Kurir pesanan belum ditentukan dengan benar.');
        }
    }

    private function catatAktivitas(
        Pesanan $pesanan,
        ?Pembayaran $pembayaran,
        $aktor,
        string $jenisAktivitas,
        ?string $statusSebelum,
        ?string $statusSesudah,
        string $deskripsi
    ): void {
        RiwayatAktivitasTransaksi::create([
            'id_pengguna' => $aktor?->id_pengguna,
            'jenis_aktor' => $aktor?->peran === 'mitra' ? 'mitra' : ($aktor ? 'admin' : 'sistem'),
            'id_pesanan' => $pesanan->id_pesanan,
            'id_pembayaran' => $pembayaran?->id_pembayaran,
            'jenis_aktivitas' => $jenisAktivitas,
            'status_sebelum' => $statusSebelum,
            'status_sesudah' => $statusSesudah,
            'deskripsi' => $deskripsi,
            'created_at' => now(),
        ]);
    }

    private function kirimNotifikasi(Pesanan $pesanan, string $judul, string $pesan, string $jenis): void
    {
        $penerima = collect([$pesanan->id_pengguna]);

        $idMitra = DB::table('pengaturan_mitra')
            ->join('pengguna', 'pengguna.id_pengguna', '=', 'pengaturan_mitra.id_pengguna')
            ->where('pengguna.peran', 'mitra')
            ->where('pengguna.status_aktif', true)
            ->pluck('pengguna.id_pengguna');

        $penerima = $penerima->merge($idMitra)->unique();

        foreach ($penerima as $idPengguna) {
            Notifikasi::create([
                'id_pengguna' => $idPengguna,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis' => $jenis,
                'jenis_referensi' => 'pesanan',
                'id_referensi' => $pesanan->id_pesanan,
                'sudah_dibaca' => false,
                'created_at' => now(),
            ]);
        }
    }
}
