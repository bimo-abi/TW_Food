<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Retur;
use App\Models\RiwayatAktivitasTransaksi;
use App\Services\RefundReturCalculationService;
use App\Services\ManualRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MitraReturController extends Controller
{
    private function pastikanMitra(Request $request): void
    {
        abort_unless($request->user() && $request->user()->peran === 'mitra' && $request->user()->status_aktif, 403, 'Akses khusus Mitra aktif.');
    }

    public function index(Request $request)
    {
        $this->pastikanMitra($request);

        $filter = $request->validate([
            'status_retur' => [
                'sometimes',
                'nullable',
                'in:menunggu_persetujuan,disetujui,menunggu_pengembalian_barang,barang_diterima,sedang_diperiksa,selesai,ditolak,dibatalkan',
            ],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Retur::query()->with([
            'pesanan:id_pesanan,nomor_pesanan,id_pengguna,status_pesanan,status_pembayaran',
            'pesanan.pengguna:id_pengguna,nama,nomor_telepon',
            'detailRetur',
            'buktiRetur',
        ])->latest('diajukan_pada');

        if (!empty($filter['status_retur'])) {
            $query->where('status_retur', $filter['status_retur']);
        }

        $perPage = (int) ($filter['per_page'] ?? 20);

        return response()->json([
            'message' => 'Daftar pengajuan retur berhasil diambil.',
            'data' => $query->paginate($perPage),
        ]);
    }

    public function show(Request $request, int $id_retur)
    {
        $this->pastikanMitra($request);
        $retur = Retur::with([
            'pesanan.pengguna', 'pesanan.detailPesanan',
            'detailRetur.detailPesanan', 'buktiRetur', 'refund.percobaanRefund',
        ])->findOrFail($id_retur);

        return response()->json(['message' => 'Detail pengajuan retur berhasil diambil.', 'data' => $retur]);
    }

    /**
     * Mitra mengirim instruksi pengembalian setelah pengajuan disetujui.
     * Instruksi disimpan agar pelanggan mengetahui cara mengembalikan barang.
     */
    public function kirimInstruksiPengembalian(Request $request, int $id_retur)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'metode_pengembalian' => ['required', 'in:outlet,kurir'],
            'instruksi_pengembalian' => ['required', 'string', 'min:5', 'max:3000'],
        ]);

        $aktor = $request->user();
        $retur = DB::transaction(function () use ($id_retur, $data, $aktor) {
            $retur = Retur::with('pesanan')
                ->where('id_retur', $id_retur)
                ->lockForUpdate()
                ->firstOrFail();

            if ($retur->status_retur !== 'disetujui') {
                throw ValidationException::withMessages([
                    'status_retur' => ['Instruksi pengembalian hanya dapat dikirim setelah retur disetujui.'],
                ]);
            }

            $statusSebelum = $retur->status_retur;
            $waktuSekarang = now();
            $retur->metode_pengembalian = $data['metode_pengembalian'];
            $retur->instruksi_pengembalian = $data['instruksi_pengembalian'];
            $retur->status_retur = 'menunggu_pengembalian_barang';
            $retur->save();

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $retur->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'retur_instruksi_pengembalian_dikirim',
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $retur->status_retur,
                'deskripsi' => 'Mitra mengirim instruksi pengembalian barang untuk retur ' . $retur->nomor_retur . '.',
                'created_at' => $waktuSekarang,
            ]);

            Notifikasi::create([
                'id_pengguna' => $retur->pesanan->id_pengguna,
                'judul' => 'Instruksi pengembalian barang tersedia',
                'pesan' => 'Retur ' . $retur->nomor_retur . ' disetujui. Metode pengembalian: '
                    . ($data['metode_pengembalian'] === 'outlet' ? 'pengembalian ke outlet' : 'melalui kurir')
                    . '. Instruksi: ' . $data['instruksi_pengembalian'],
                'jenis' => 'retur',
                'jenis_referensi' => 'retur',
                'id_referensi' => $retur->id_retur,
                'sudah_dibaca' => false,
                'created_at' => $waktuSekarang,
            ]);

            return $retur->fresh(['pesanan.pengguna', 'detailRetur.detailPesanan', 'buktiRetur']);
        });

        return response()->json([
            'message' => 'Instruksi pengembalian barang berhasil dikirim kepada pelanggan.',
            'data' => $retur,
        ]);
    }

    /**
     * Mitra mengonfirmasi bahwa barang retur telah diterima secara fisik.
     * Pemeriksaan kondisi barang dan keputusan stok dilakukan pada tahap berikutnya.
     */
    public function konfirmasiBarangDiterima(Request $request, int $id_retur)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'catatan_penerimaan' => ['nullable', 'string', 'max:2000'],
        ]);

        $aktor = $request->user();

        $retur = DB::transaction(function () use ($id_retur, $data, $aktor) {
            $retur = Retur::with('pesanan')
                ->where('id_retur', $id_retur)
                ->lockForUpdate()
                ->firstOrFail();

            if ($retur->status_retur !== 'menunggu_pengembalian_barang') {
                throw ValidationException::withMessages([
                    'status_retur' => ['Barang hanya dapat dikonfirmasi diterima ketika retur berstatus menunggu_pengembalian_barang.'],
                ]);
            }

            $statusSebelum = $retur->status_retur;
            $waktuSekarang = now();

            $retur->status_retur = 'barang_diterima';
            $retur->barang_diterima_pada = $waktuSekarang;

            if (!empty($data['catatan_penerimaan'])) {
                $catatanLama = trim((string) $retur->catatan_mitra);
                $catatanBaru = 'Penerimaan barang: ' . trim($data['catatan_penerimaan']);
                $retur->catatan_mitra = $catatanLama !== ''
                    ? $catatanLama . "\n" . $catatanBaru
                    : $catatanBaru;
            }

            $retur->save();

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $retur->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'retur_barang_diterima',
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $retur->status_retur,
                'deskripsi' => 'Mitra mengonfirmasi barang retur ' . $retur->nomor_retur . ' telah diterima.',
                'created_at' => $waktuSekarang,
            ]);

            Notifikasi::create([
                'id_pengguna' => $retur->pesanan->id_pengguna,
                'judul' => 'Barang retur telah diterima Mitra',
                'pesan' => 'Barang untuk retur ' . $retur->nomor_retur . ' telah diterima Mitra dan akan memasuki proses pemeriksaan.',
                'jenis' => 'retur',
                'jenis_referensi' => 'retur',
                'id_referensi' => $retur->id_retur,
                'sudah_dibaca' => false,
                'created_at' => $waktuSekarang,
            ]);

            return $retur->fresh(['pesanan.pengguna', 'detailRetur.detailPesanan', 'buktiRetur']);
        });

        return response()->json([
            'message' => 'Penerimaan barang retur berhasil dikonfirmasi. Barang menunggu pemeriksaan Mitra.',
            'data' => $retur,
        ]);
    }


    /**
     * Mitra menyelesaikan pemeriksaan fisik item retur dan mencatat jumlah
     * yang layak dijual. Hanya jumlah tersebut yang dikembalikan ke stok.
     * Status retur tetap sedang_diperiksa sampai proses refund ditangani.
     */
    public function periksaBarangRetur(Request $request, int $id_retur, RefundReturCalculationService $refundService)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'catatan_pemeriksaan' => ['nullable', 'string', 'max:2000'],
            'detail_inspeksi' => ['required', 'array', 'min:1'],
            'detail_inspeksi.*.id_detail_retur' => ['required', 'integer', 'distinct'],
            'detail_inspeksi.*.jumlah_layak_dijual' => ['required', 'integer', 'min:0', 'max:65535'],
        ]);

        $aktor = $request->user();

        $retur = DB::transaction(function () use ($id_retur, $data, $aktor, $refundService) {
            $retur = Retur::with('pesanan')
                ->where('id_retur', $id_retur)
                ->lockForUpdate()
                ->firstOrFail();

            if ($retur->status_retur !== 'barang_diterima') {
                throw ValidationException::withMessages([
                    'status_retur' => ['Pemeriksaan hanya dapat dilakukan setelah barang retur dikonfirmasi diterima.'],
                ]);
            }

            $detailTersimpan = $retur->detailRetur()
                ->with('detailPesanan')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_detail_retur');

            $detailMasuk = collect($data['detail_inspeksi']);
            $idDetailMasuk = $detailMasuk
                ->pluck('id_detail_retur')
                ->map(fn ($id) => (int) $id)
                ->all();

            $idDetailTersimpan = $detailTersimpan->keys()
                ->map(fn ($id) => (int) $id)
                ->sort()->values()->all();

            if (count($idDetailMasuk) !== $detailTersimpan->count()
                || count(array_unique($idDetailMasuk)) !== $detailTersimpan->count()
                || collect($idDetailMasuk)->sort()->values()->all() !== $idDetailTersimpan) {
                throw ValidationException::withMessages([
                    'detail_inspeksi' => ['Seluruh item retur harus diperiksa tepat satu kali.'],
                ]);
            }

            $waktuSekarang = now();
            $totalLayakDijual = 0;

            foreach ($detailMasuk as $item) {
                $idDetailRetur = (int) $item['id_detail_retur'];
                $jumlahLayak = (int) $item['jumlah_layak_dijual'];
                $detailRetur = $detailTersimpan->get($idDetailRetur);
                $jumlahDisetujui = (int) ($detailRetur->jumlah_disetujui ?? 0);

                if ($jumlahLayak > $jumlahDisetujui) {
                    throw ValidationException::withMessages([
                        'detail_inspeksi' => ['Jumlah barang layak dijual tidak boleh melebihi jumlah retur yang disetujui.'],
                    ]);
                }

                if ($jumlahLayak > 0) {
                    $detailPesanan = $detailRetur->detailPesanan;
                    if (!$detailPesanan || !$detailPesanan->id_varian) {
                        throw ValidationException::withMessages([
                            'detail_inspeksi' => ['Varian produk untuk item retur tidak ditemukan sehingga stok tidak dapat diperbarui.'],
                        ]);
                    }

                    $varian = DB::table('varian_produk')
                        ->where('id_varian', $detailPesanan->id_varian)
                        ->lockForUpdate()
                        ->first();

                    if (!$varian) {
                        throw ValidationException::withMessages([
                            'detail_inspeksi' => ['Varian produk tidak ditemukan.'],
                        ]);
                    }

                    $stokBaru = (int) $varian->stok + $jumlahLayak;
                    if ($stokBaru > 16777215) {
                        throw ValidationException::withMessages([
                            'detail_inspeksi' => ['Penambahan stok melebihi kapasitas kolom stok varian produk.'],
                        ]);
                    }

                    DB::table('varian_produk')
                        ->where('id_varian', $detailPesanan->id_varian)
                        ->update([
                            'stok' => $stokBaru,
                            'updated_at' => $waktuSekarang,
                        ]);
                }

                $detailRetur->jumlah_layak_dijual = $jumlahLayak;
                if ($jumlahDisetujui === 0) {
                    // Item ini tidak disetujui untuk diretur, jadi tidak ikut inspeksi stok.
                    $detailRetur->status_kelayakan_stok = 'belum_diperiksa';
                } else {
                    // Jumlah parsial tetap tercatat pada jumlah_layak_dijual.
                    $detailRetur->status_kelayakan_stok = $jumlahLayak > 0
                        ? 'layak_dijual'
                        : 'tidak_layak';
                }
                $detailRetur->save();
                $totalLayakDijual += $jumlahLayak;
            }

            $statusSebelum = $retur->status_retur;
            $retur->status_retur = 'sedang_diperiksa';
            $retur->diperiksa_pada = $waktuSekarang;

            if (!empty($data['catatan_pemeriksaan'])) {
                $catatanLama = trim((string) $retur->catatan_mitra);
                $catatanBaru = 'Hasil pemeriksaan: ' . trim($data['catatan_pemeriksaan']);
                $retur->catatan_mitra = $catatanLama !== ''
                    ? $catatanLama . "\n" . $catatanBaru
                    : $catatanBaru;
            }

            $retur->save();

            // Setelah pemeriksaan barang selesai, Laravel menghitung nominal
            // refund dan menyiapkan catatan refund manual tanpa memanggil Midtrans.
            $hasilRefund = $refundService->hitungDanCatat($retur, $aktor);

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $retur->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'retur_pemeriksaan_selesai',
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $retur->status_retur,
                'deskripsi' => 'Mitra memeriksa barang retur ' . $retur->nomor_retur
                    . '. Jumlah barang layak dijual yang dikembalikan ke stok: ' . $totalLayakDijual
                    . '. Nominal refund yang disiapkan: Rp' . number_format($hasilRefund['nominal_refund'], 0, ',', '.') . '.',
                'created_at' => $waktuSekarang,
            ]);

            Notifikasi::create([
                'id_pengguna' => $retur->pesanan->id_pengguna,
                'judul' => 'Pemeriksaan barang retur selesai',
                'pesan' => 'Mitra telah memeriksa barang retur ' . $retur->nomor_retur
                    . '. Nominal refund yang dihitung: Rp' . number_format($hasilRefund['nominal_refund'], 0, ',', '.')
                    . '. Pengembalian dana sedang menunggu diproses secara manual.',
                'jenis' => 'retur',
                'jenis_referensi' => 'retur',
                'id_referensi' => $retur->id_retur,
                'sudah_dibaca' => false,
                'created_at' => $waktuSekarang,
            ]);

            return $retur->fresh(['pesanan.pengguna', 'detailRetur.detailPesanan.varian', 'buktiRetur']);
        });

        return response()->json([
            'message' => 'Pemeriksaan barang retur berhasil dicatat. Nominal refund telah dihitung dan disiapkan untuk proses manual.',
            'data' => $retur,
            'refund' => [
                'nominal_total' => $retur->nominal_pengembalian,
                'status' => 'menunggu_diproses',
                'rincian' => $retur->refund()->with('pembayaran')->get(),
            ],
        ]);
    }


    /**
     * Mencatat hasil refund yang sudah dilakukan secara manual oleh Mitra.
     * Admin Website menggunakan service yang sama melalui route web.
     */
    public function catatRefundManual(Request $request, int $id_refund, ManualRefundService $manualRefundService)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'hasil' => ['required', 'in:berhasil,gagal'],
            'metode_refund' => ['required', 'in:midtrans_dashboard,transfer_bank,lainnya'],
            'referensi_transaksi' => ['required_if:hasil,berhasil', 'nullable', 'string', 'max:100'],
            'catatan' => ['required_if:hasil,gagal', 'nullable', 'string', 'min:5', 'max:2000'],
            'bukti_refund' => ['required_if:hasil,berhasil', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $hasil = $manualRefundService->catatHasil(
            $id_refund,
            $request->user(),
            $data,
            $request->file('bukti_refund')
        );

        return response()->json([
            'message' => $data['hasil'] === 'berhasil'
                ? 'Hasil refund manual berhasil dicatat.'
                : 'Kegagalan percobaan refund manual berhasil dicatat. Refund dapat ditindaklanjuti atau dicoba kembali.',
            'data' => $hasil,
        ]);
    }

    /**
     * Mitra menyetujui atau menolak pengajuan retur pelanggan.
     * Persetujuan hanya mencatat jumlah yang disetujui; proses pengembalian
     * barang, pemeriksaan fisik, penyesuaian stok, dan refund dilakukan pada tahap terpisah.
     */
    public function updateKeputusan(Request $request, int $id_retur)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'keputusan' => ['required', 'in:disetujui,ditolak'],
            'catatan_mitra' => ['nullable', 'string', 'max:2000'],
            'alasan_penolakan' => ['required_if:keputusan,ditolak', 'nullable', 'string', 'min:5', 'max:2000'],
            'detail_retur' => ['required_if:keputusan,disetujui', 'array', 'min:1'],
            'detail_retur.*.id_detail_retur' => ['required_with:detail_retur', 'integer', 'distinct'],
            'detail_retur.*.jumlah_disetujui' => ['required_with:detail_retur', 'integer', 'min:0', 'max:65535'],
        ]);

        $aktor = $request->user();

        $retur = DB::transaction(function () use ($id_retur, $data, $aktor) {
            $retur = Retur::with('pesanan')
                ->where('id_retur', $id_retur)
                ->lockForUpdate()
                ->firstOrFail();

            if ($retur->status_retur !== 'menunggu_persetujuan') {
                throw ValidationException::withMessages([
                    'status_retur' => ['Pengajuan retur hanya dapat diputuskan ketika berstatus menunggu_persetujuan.'],
                ]);
            }

            $statusSebelum = $retur->status_retur;
            $keputusan = $data['keputusan'];
            $waktuSekarang = now();

            if ($keputusan === 'disetujui') {
                $detailTersimpan = $retur->detailRetur()
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_detail_retur');

                $detailMasuk = collect($data['detail_retur'] ?? []);
                $idDetailMasuk = $detailMasuk
                    ->pluck('id_detail_retur')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (count($idDetailMasuk) !== $detailTersimpan->count()
                    || count(array_unique($idDetailMasuk)) !== $detailTersimpan->count()
                    || $detailTersimpan->keys()->map(fn ($id) => (int) $id)->sort()->values()->all()
                        !== collect($idDetailMasuk)->sort()->values()->all()) {
                    throw ValidationException::withMessages([
                        'detail_retur' => ['Seluruh item retur harus dikirim tepat satu kali untuk diputuskan.'],
                    ]);
                }

                $totalJumlahDisetujui = 0;

                foreach ($detailMasuk as $item) {
                    $idDetailRetur = (int) $item['id_detail_retur'];
                    $jumlahDisetujui = (int) $item['jumlah_disetujui'];
                    $detail = $detailTersimpan->get($idDetailRetur);
                    $jumlahDiminta = (int) ($detail->jumlah_diminta ?? $detail->jumlah_diretur);

                    if ($jumlahDisetujui > $jumlahDiminta) {
                        throw ValidationException::withMessages([
                            'detail_retur' => ['Jumlah yang disetujui tidak boleh melebihi jumlah yang diminta pelanggan.'],
                        ]);
                    }

                    $detail->jumlah_disetujui = $jumlahDisetujui;
                    $detail->save();
                    $totalJumlahDisetujui += $jumlahDisetujui;
                }

                if ($totalJumlahDisetujui < 1) {
                    throw ValidationException::withMessages([
                        'detail_retur' => ['Minimal satu item harus disetujui dengan jumlah lebih dari nol.'],
                    ]);
                }

                $retur->status_retur = 'disetujui';
                $retur->disetujui_pada = $waktuSekarang;
                $retur->alasan_penolakan = null;
            } else {
                $retur->status_retur = 'ditolak';
                $retur->alasan_penolakan = $data['alasan_penolakan'];
            }

            $retur->catatan_mitra = $data['catatan_mitra'] ?? null;
            $retur->save();

            $labelKeputusan = $keputusan === 'disetujui' ? 'disetujui' : 'ditolak';
            $pesanPelanggan = $keputusan === 'disetujui'
                ? 'Pengajuan retur ' . $retur->nomor_retur . ' telah disetujui. Mitra akan memberikan instruksi pengembalian barang.'
                : 'Pengajuan retur ' . $retur->nomor_retur . ' ditolak. Alasan: ' . $data['alasan_penolakan'];

            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor->id_pengguna,
                'jenis_aktor' => 'mitra',
                'id_pesanan' => $retur->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'retur_' . $labelKeputusan,
                'status_sebelum' => $statusSebelum,
                'status_sesudah' => $retur->status_retur,
                'deskripsi' => 'Mitra ' . $labelKeputusan . ' pengajuan retur ' . $retur->nomor_retur . '.',
                'created_at' => $waktuSekarang,
            ]);

            Notifikasi::create([
                'id_pengguna' => $retur->pesanan->id_pengguna,
                'judul' => $keputusan === 'disetujui' ? 'Pengajuan retur disetujui' : 'Pengajuan retur ditolak',
                'pesan' => $pesanPelanggan,
                'jenis' => 'retur',
                'jenis_referensi' => 'retur',
                'id_referensi' => $retur->id_retur,
                'sudah_dibaca' => false,
                'created_at' => $waktuSekarang,
            ]);

            return $retur->fresh(['pesanan.pengguna', 'detailRetur.detailPesanan', 'buktiRetur']);
        });

        return response()->json([
            'message' => $data['keputusan'] === 'disetujui'
                ? 'Pengajuan retur berhasil disetujui.'
                : 'Pengajuan retur berhasil ditolak.',
            'data' => $retur,
        ]);
    }

}
