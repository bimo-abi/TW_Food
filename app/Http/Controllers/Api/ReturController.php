<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetailPesanan;
use App\Models\DetailRetur;
use App\Models\Pembayaran;
use App\Models\Notifikasi;
use App\Models\Pesanan;
use App\Models\Retur;
use App\Models\BuktiRetur;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ReturController extends Controller
{
    /**
     * Mengajukan retur untuk pesanan milik pelanggan yang sudah diterima dan lunas.
     */
    public function store(Request $request, int $id_pesanan)
    {
        $pengguna = $request->user();

        // Endpoint retur hanya boleh digunakan pelanggan aktif.
        if (!$pengguna || !$pengguna->status_aktif || $pengguna->peran !== 'pelanggan') {
            return response()->json([
                'message' => 'Pengajuan retur hanya dapat dilakukan oleh akun pelanggan aktif.',
            ], 403);
        }

        $validated = $request->validate([
            'alasan_retur' => ['required', 'string', 'min:5', 'max:2000'],
            'detail_retur' => ['required', 'array', 'min:1'],
            'detail_retur.*.id_detail_pesanan' => ['required', 'integer', 'distinct'],
            'detail_retur.*.jumlah_diminta' => ['required', 'integer', 'min:1', 'max:65535'],
            'detail_retur.*.alasan_item' => ['nullable', 'string', 'max:1000'],
            'bukti' => ['required', 'array', 'min:1', 'max:5'],
            'bukti.*' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,avi',
                'max:20480',
            ],
        ]);

        // Maksimal 5 bukti, masing-masing 20 MB dan total 50 MB.
        // Batas total mencegah satu pengajuan membebani penyimpanan server.
        $totalUkuranBukti = collect($request->file('bukti', []))
            ->sum(fn ($file) => (int) $file->getSize());

        if ($totalUkuranBukti > 50 * 1024 * 1024) {
            return response()->json([
                'message' => 'Total ukuran seluruh bukti retur tidak boleh melebihi 50 MB.',
            ], 422);
        }

        $nomorRetur = 'RTR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        $fileTersimpan = [];

        try {
            $hasil = DB::transaction(function () use (
                $id_pesanan,
                $pengguna,
                $validated,
                $nomorRetur,
                $request,
                &$fileTersimpan
            ) {
                $pesanan = Pesanan::where('id_pesanan', $id_pesanan)
                    ->where('id_pengguna', $pengguna->id_pengguna)
                    ->lockForUpdate()
                    ->first();

                if (!$pesanan) {
                    return [
                        'error' => 'Pesanan tidak ditemukan.',
                        'status' => 404,
                    ];
                }

                if ($pesanan->status_pesanan !== 'diterima' || !$pesanan->diterima_pada) {
                    return [
                        'error' => 'Retur hanya dapat diajukan setelah pesanan berstatus diterima.',
                        'status' => 422,
                    ];
                }

                if (now()->greaterThan($pesanan->diterima_pada->copy()->addDay())) {
                    return [
                        'error' => 'Batas pengajuan retur 1 x 24 jam sejak pesanan diterima telah berakhir.',
                        'status' => 422,
                    ];
                }

                if ($pesanan->status_pembayaran !== 'lunas') {
                    return [
                        'error' => 'Retur hanya dapat diajukan untuk pesanan yang sudah lunas.',
                        'status' => 422,
                    ];
                }

                $totalPembayaranBerhasil = (int) Pembayaran::where('id_pesanan', $pesanan->id_pesanan)
                    ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
                    ->where('status_pembayaran', 'paid')
                    ->sum('jumlah_pembayaran');

                if ($totalPembayaranBerhasil < (int) $pesanan->total_pesanan) {
                    return [
                        'error' => 'Total pembayaran terverifikasi belum memenuhi total pesanan.',
                        'status' => 422,
                    ];
                }

                // PRD v1.6 membatasi satu permintaan retur aktif per pesanan.
                // Baris pesanan sudah dikunci dengan lockForUpdate(), sehingga
                // dua pengajuan bersamaan untuk pesanan yang sama tidak lolos.
                $adaReturAktif = Retur::query()
                    ->where('id_pesanan', $pesanan->id_pesanan)
                    ->whereIn('status_retur', [
                        'diajukan',
                        'diproses',
                        'menunggu_persetujuan',
                        'disetujui',
                        'menunggu_pengembalian_barang',
                        'barang_diterima',
                        'sedang_diperiksa',
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($adaReturAktif) {
                    return [
                        'error' => 'Pesanan ini masih memiliki pengajuan retur aktif. Selesaikan atau tunggu keputusan pengajuan tersebut sebelum mengajukan retur baru.',
                        'status' => 422,
                    ];
                }

                // Pengajuan yang masih menunggu keputusan Mitra menahan
                // jumlah yang diminta. Setelah disetujui, yang dihitung
                // hanya jumlah_disetujui, bukan seluruh jumlah_diminta.
                // Retur ditolak/dibatalkan tidak mengurangi kuota retur.
                $statusReturMenungguKeputusan = [
                    'diajukan',
                    'diproses',
                    'menunggu_persetujuan',
                ];

                $statusReturSudahDisetujui = [
                    'disetujui',
                    'menunggu_pengembalian_barang',
                    'barang_diterima',
                    'sedang_diperiksa',
                    'selesai',
                ];

                $statusReturPenghitung = array_merge(
                    $statusReturMenungguKeputusan,
                    $statusReturSudahDisetujui
                );

                $detailIds = collect($validated['detail_retur'])
                    ->pluck('id_detail_pesanan')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $detailPesanan = DetailPesanan::where('id_pesanan', $pesanan->id_pesanan)
                    ->whereIn('id_detail_pesanan', $detailIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_detail_pesanan');

                if ($detailPesanan->count() !== count($detailIds)) {
                    return [
                        'error' => 'Satu atau lebih item bukan bagian dari pesanan ini.',
                        'status' => 422,
                    ];
                }

                foreach ($validated['detail_retur'] as $item) {
                    $idDetail = (int) $item['id_detail_pesanan'];
                    $jumlahDiminta = (int) $item['jumlah_diminta'];
                    $detail = $detailPesanan->get($idDetail);

                    $statusDisetujuiSql = implode(',', array_map(
                        fn ($status) => DB::getPdo()->quote($status),
                        $statusReturSudahDisetujui
                    ));

                    $jumlahSebelumnya = (int) DB::table('detail_retur')
                        ->join('retur', 'retur.id_retur', '=', 'detail_retur.id_retur')
                        ->where('retur.id_pesanan', $pesanan->id_pesanan)
                        ->where('detail_retur.id_detail_pesanan', $idDetail)
                        ->whereIn('retur.status_retur', $statusReturPenghitung)
                        ->sum(DB::raw(
                            'CASE '
                            . 'WHEN retur.status_retur IN (' . $statusDisetujuiSql . ') '
                            . 'THEN COALESCE(detail_retur.jumlah_disetujui, detail_retur.jumlah_diminta, detail_retur.jumlah_diretur) '
                            . 'ELSE COALESCE(detail_retur.jumlah_diminta, detail_retur.jumlah_diretur) '
                            . 'END'
                        ));

                    $sisaJumlah = (int) $detail->jumlah - $jumlahSebelumnya;

                    if ($jumlahDiminta > $sisaJumlah) {
                        return [
                            'error' => 'Jumlah retur melebihi sisa jumlah item yang masih dapat diretur: ' . $detail->nama_produk_saat_pesan . '.',
                            'status' => 422,
                        ];
                    }
                }

                $retur = Retur::create([
                    'id_pesanan' => $pesanan->id_pesanan,
                    'nomor_retur' => $nomorRetur,
                    'alasan_retur' => $validated['alasan_retur'],
                    'status_retur' => 'menunggu_persetujuan',
                    'status_pengembalian_dana' => 'tidak_ada',
                    'nominal_pengembalian' => 0,
                    'diajukan_pada' => now(),
                ]);

                foreach ($validated['detail_retur'] as $item) {
                    $detail = $detailPesanan->get((int) $item['id_detail_pesanan']);

                    DetailRetur::create([
                        'id_retur' => $retur->id_retur,
                        'id_detail_pesanan' => $detail->id_detail_pesanan,
                        'jumlah_diretur' => (int) $item['jumlah_diminta'],
                        'jumlah_diminta' => (int) $item['jumlah_diminta'],
                        'jumlah_disetujui' => null,
                        'alasan_item' => $item['alasan_item'] ?? null,
                        'nominal_refund' => 0,
                        'diskon_dialokasikan' => 0,
                        'status_kelayakan_stok' => 'belum_diperiksa',
                    ]);
                }

                foreach ($request->file('bukti', []) as $file) {
                    $path = $file->store('retur/' . $nomorRetur, 'local');
                    if (!$path) {
                        throw new \RuntimeException('Gagal menyimpan file bukti retur.');
                    }

                    $fileTersimpan[] = $path;

                    $mime = $file->getMimeType() ?? '';
                    $jenisMedia = str_starts_with($mime, 'video/') ? 'video' : 'foto';

                    BuktiRetur::create([
                        'id_retur' => $retur->id_retur,
                        'jenis_media' => $jenisMedia,
                        'file_bukti' => $path,
                    ]);
                }

                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => $pengguna->id_pengguna,
                    'jenis_aktor' => 'pelanggan',
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_pembayaran' => null,
                    'jenis_aktivitas' => 'retur_diajukan',
                    'status_sebelum' => null,
                    'status_sesudah' => 'menunggu_persetujuan',
                    'deskripsi' => 'Pelanggan mengajukan retur ' . $retur->nomor_retur . '.',
                    'created_at' => now(),
                ]);

                $dataNotifikasi = [
                    'judul' => 'Pengajuan retur baru',
                    'pesan' => 'Pelanggan mengajukan retur untuk pesanan ' . $pesanan->nomor_pesanan . '.',
                    'jenis' => 'retur',
                    'jenis_referensi' => 'retur',
                    'id_referensi' => $retur->id_retur,
                    'sudah_dibaca' => false,
                    'created_at' => now(),
                ];

                // Kirim notifikasi in-app ke seluruh akun Mitra aktif,
                // termasuk Mitra yang belum memiliki baris pengaturan_mitra.
                $idMitraAktif = DB::table('pengguna')
                    ->where('peran', 'mitra')
                    ->where('status_aktif', true)
                    ->pluck('id_pengguna');

                foreach ($idMitraAktif as $idPenggunaMitra) {
                    Notifikasi::create(array_merge($dataNotifikasi, [
                        'id_pengguna' => $idPenggunaMitra,
                    ]));
                }

                return [
                    'retur' => $retur->load(['detailRetur', 'buktiRetur']),
                    'nomor_pesanan' => $pesanan->nomor_pesanan,
                ];
            });

            if (isset($hasil['error'])) {
                foreach ($fileTersimpan as $path) {
                    Storage::disk('local')->delete($path);
                }

                return response()->json([
                    'message' => $hasil['error'],
                ], $hasil['status']);
            }

            return response()->json([
                'message' => 'Pengajuan retur berhasil dikirim dan menunggu persetujuan Mitra.',
                'data' => $hasil['retur'],
            ], 201);
        } catch (Throwable $e) {
            foreach ($fileTersimpan as $path) {
                Storage::disk('local')->delete($path);
            }

            report($e);

            return response()->json([
                'message' => 'Pengajuan retur gagal diproses. Silakan coba kembali.',
            ], 500);
        }
    }
}
