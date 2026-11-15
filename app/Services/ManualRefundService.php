<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\Pengguna;
use App\Models\Pesanan;
use App\Models\Refund;
use App\Models\Retur;
use App\Models\PercobaanRefund;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManualRefundService
{
    /**
     * Mencatat hasil pengembalian dana yang sudah dilakukan secara manual
     * melalui Midtrans MAP atau transfer bank. Tidak memanggil Refund API.
     */
    public function catatHasil(
        int $idRefund,
        Pengguna $aktor,
        array $data,
        ?UploadedFile $buktiRefund = null
    ): array {
        $pathBukti = null;

        try {
            return DB::transaction(function () use (
                $idRefund,
                $aktor,
                $data,
                $buktiRefund,
                &$pathBukti
            ) {
                // Urutan lock dibuat konsisten: retur -> pesanan -> refund.
                // Ini mencegah dua refund untuk satu retur/pesanan diproses
                // bersamaan dengan snapshot status yang sudah kedaluwarsa.
                $idRetur = Refund::query()->where('id_refund', $idRefund)->value('id_retur');
                if (!$idRetur) {
                    throw ValidationException::withMessages([
                        'refund' => ['Refund retur tidak ditemukan.'],
                    ]);
                }

                $retur = Retur::query()->where('id_retur', $idRetur)
                    ->lockForUpdate()->firstOrFail();
                $pesanan = Pesanan::query()->where('id_pesanan', $retur->id_pesanan)
                    ->lockForUpdate()->firstOrFail();
                $refund = Refund::query()->where('id_refund', $idRefund)
                    ->lockForUpdate()->firstOrFail();

                if ($refund->jenis_refund !== 'retur' || !$refund->id_retur || (int) $refund->id_retur !== (int) $retur->id_retur) {
                    throw ValidationException::withMessages([
                        'refund' => ['Pencatatan manual ini hanya berlaku untuk refund yang berasal dari retur.'],
                    ]);
                }

                // Refund retur baru boleh diproses setelah pemeriksaan barang selesai.
                if ($retur->status_retur !== 'sedang_diperiksa') {
                    throw ValidationException::withMessages([
                        'status_retur' => ['Refund hanya dapat dicatat setelah pemeriksaan barang retur selesai.'],
                    ]);
                }

                $semuaRefundRetur = Refund::query()
                    ->where('id_retur', $refund->id_retur)
                    ->lockForUpdate()
                    ->get();

                $nominalSeluruhRefund = (int) $semuaRefundRetur->sum('nominal_refund');
                if ($semuaRefundRetur->isEmpty()
                    || $nominalSeluruhRefund !== (int) $retur->nominal_pengembalian
                    || $semuaRefundRetur->contains(fn (Refund $item) =>
                        (int) $item->id_pesanan !== (int) $refund->id_pesanan
                        || !$item->id_pembayaran
                    )) {
                    throw ValidationException::withMessages([
                        'refund' => ['Data alokasi refund tidak konsisten dengan nominal pengembalian pada retur.'],
                    ]);
                }

                $pembayaranSumber = Pembayaran::query()
                    ->where('id_pembayaran', $refund->id_pembayaran)
                    ->where('id_pesanan', $refund->id_pesanan)
                    ->where('status_pembayaran', 'paid')
                    ->lockForUpdate()
                    ->first();

                if (!$pembayaranSumber || (int) $refund->nominal_refund > (int) $pembayaranSumber->jumlah_pembayaran) {
                    throw ValidationException::withMessages([
                        'pembayaran' => ['Transaksi pembayaran sumber tidak valid atau nominal refund melebihi pembayaran.'],
                    ]);
                }

                $alokasiAktifDanBerhasil = (int) Refund::query()
                    ->where('id_pembayaran', $refund->id_pembayaran)
                    ->whereIn('status_refund', [
                        'belum_diajukan', 'menunggu_diproses', 'diproses',
                        'berhasil', 'gagal', 'perlu_diproses_ulang',
                    ])
                    ->sum('nominal_refund');

                if ($alokasiAktifDanBerhasil > (int) $pembayaranSumber->jumlah_pembayaran) {
                    throw ValidationException::withMessages([
                        'pembayaran' => ['Akumulasi alokasi refund pada transaksi pembayaran melebihi nominal pembayaran.'],
                    ]);
                }

                if (!in_array($refund->status_refund, [
                    'menunggu_diproses',
                    'gagal',
                    'perlu_diproses_ulang',
                ], true)) {
                    throw ValidationException::withMessages([
                        'status_refund' => ['Refund ini tidak berada pada status yang dapat diproses atau dicoba ulang.'],
                    ]);
                }

                if ((int) $refund->nominal_refund < 1) {
                    throw ValidationException::withMessages([
                        'nominal_refund' => ['Nominal refund harus lebih besar dari nol.'],
                    ]);
                }

                if ($data['hasil'] === 'berhasil' && !$buktiRefund) {
                    throw ValidationException::withMessages([
                        'bukti_refund' => ['Bukti pengembalian dana wajib diunggah untuk mencatat refund berhasil.'],
                    ]);
                }

                $waktuSekarang = now();
                $statusSebelum = $refund->status_refund;
                $idPercobaan = (string) Str::uuid();

                if ($buktiRefund) {
                    $extension = strtolower($buktiRefund->getClientOriginalExtension() ?: 'bin');
                    $namaFile = $idPercobaan . '.' . $extension;
                    $pathBukti = $buktiRefund->storeAs(
                        'refund-manual/' . $refund->id_refund,
                        $namaFile,
                        'local'
                    );

                    if (!$pathBukti) {
                        throw ValidationException::withMessages([
                            'bukti_refund' => ['Bukti refund gagal disimpan. Silakan coba kembali.'],
                        ]);
                    }
                }

                $berhasil = $data['hasil'] === 'berhasil';
                $provider = match ($data['metode_refund']) {
                    'midtrans_dashboard' => 'Midtrans Manual',
                    'transfer_bank' => 'Transfer Bank Manual',
                    default => 'Metode Manual Lainnya',
                };

                $percobaan = PercobaanRefund::create([
                    'id_refund' => $refund->id_refund,
                    'kunci_percobaan' => 'manual-' . $refund->id_refund . '-' . $idPercobaan,
                    'penyedia_pembayaran' => $provider,
                    'nominal_refund' => $refund->nominal_refund,
                    'status_percobaan' => $berhasil ? 'berhasil' : 'gagal',
                    'referensi_gateway' => $data['referensi_transaksi'] ?? null,
                    'respons_gateway' => [
                        'mode' => 'manual',
                        'metode_refund' => $data['metode_refund'],
                        'bukti_path' => $pathBukti,
                        'dicatat_oleh' => $aktor->id_pengguna,
                        'catatan' => $data['catatan'] ?? null,
                    ],
                    'pesan_error' => $berhasil ? null : ($data['catatan'] ?? 'Pengembalian dana manual gagal.'),
                    'dimulai_pada' => $waktuSekarang,
                    'selesai_pada' => $waktuSekarang,
                ]);

                $refund->status_refund = $berhasil ? 'berhasil' : 'gagal';
                $refund->diproses_pada = $refund->diproses_pada ?? $waktuSekarang;
                $refund->selesai_pada = $berhasil ? $waktuSekarang : null;
                $refund->save();

                $semuaRefundRetur = Refund::query()
                    ->where('id_retur', $retur->id_retur)
                    ->get();

                $semuaBerhasil = $semuaRefundRetur->isNotEmpty()
                    && $semuaRefundRetur->every(
                        fn (Refund $item) => $item->status_refund === 'berhasil'
                    );

                if ($semuaBerhasil) {
                    $retur->status_pengembalian_dana = 'selesai';
                    $retur->status_retur = 'selesai';
                    $retur->selesai_pada = $waktuSekarang;
                } else {
                    $statusRefund = $semuaRefundRetur->pluck('status_refund');
                    $adaAktif = $statusRefund->contains(fn ($status) => in_array(
                        $status,
                        ['belum_diajukan', 'menunggu_diproses', 'diproses', 'perlu_diproses_ulang'],
                        true
                    ));
                    $adaGagal = $statusRefund->contains('gagal');

                    if ($adaAktif && $adaGagal) {
                        $retur->status_pengembalian_dana = 'diproses';
                    } elseif ($adaAktif && $statusRefund->every(fn ($status) => $status === 'menunggu_diproses')) {
                        $retur->status_pengembalian_dana = 'menunggu';
                    } elseif ($adaAktif) {
                        $retur->status_pengembalian_dana = 'diproses';
                    } else {
                        $retur->status_pengembalian_dana = 'gagal';
                    }
                }
                $retur->save();

                // Status pembayaran pesanan hanya menjadi dikembalikan apabila
                // seluruh dana pada seluruh transaksi pembayaran sudah berhasil
                // dikembalikan. Status transaksi pembayaran asli tidak diubah.
                $seluruhPembayaran = Pembayaran::query()
                    ->where('id_pesanan', $refund->id_pesanan)
                    ->where('status_pembayaran', 'paid')
                    ->lockForUpdate()
                    ->get();

                $seluruhDanaSudahKembali = $seluruhPembayaran->isNotEmpty();

                foreach ($seluruhPembayaran as $pembayaran) {
                    $nominalBerhasilDikembalikan = Refund::query()
                        ->where('id_pembayaran', $pembayaran->id_pembayaran)
                        ->where('status_refund', 'berhasil')
                        ->sum('nominal_refund');

                    if ((int) $nominalBerhasilDikembalikan < (int) $pembayaran->jumlah_pembayaran) {
                        $seluruhDanaSudahKembali = false;
                        break;
                    }
                }

                if ($seluruhDanaSudahKembali && $pesanan) {
                    $pesanan->status_pembayaran = 'dikembalikan';
                    $pesanan->save();
                }

                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => $aktor->id_pengguna,
                    'jenis_aktor' => $aktor->peran === 'mitra' ? 'mitra' : 'admin',
                    'id_pesanan' => $refund->id_pesanan,
                    'id_pembayaran' => $refund->id_pembayaran,
                    'jenis_aktivitas' => $berhasil ? 'refund_manual_berhasil' : 'refund_manual_gagal',
                    'status_sebelum' => $statusSebelum,
                    'status_sesudah' => $refund->status_refund,
                    'deskripsi' => ($berhasil ? 'Refund manual berhasil dicatat' : 'Percobaan refund manual gagal')
                        . ' untuk retur ' . $retur->nomor_retur
                        . ' sebesar Rp' . number_format((int) $refund->nominal_refund, 0, ',', '.')
                        . ' oleh ' . $aktor->nama . '.',
                    'created_at' => $waktuSekarang,
                ]);

                $judul = $berhasil ? 'Pengembalian dana retur berhasil' : 'Pengembalian dana retur belum berhasil';
                $pesan = $berhasil
                    ? 'Pengembalian dana untuk retur ' . $retur->nomor_retur
                        . ' sebesar Rp' . number_format((int) $refund->nominal_refund, 0, ',', '.')
                        . ' telah dicatat berhasil.'
                    : 'Percobaan pengembalian dana untuk retur ' . $retur->nomor_retur
                        . ' belum berhasil. Pengembalian dana dapat ditindaklanjuti atau dicoba kembali.';

                Notifikasi::create([
                    'id_pengguna' => $pesanan->id_pengguna,
                    'judul' => $judul,
                    'pesan' => $pesan,
                    'jenis' => 'retur',
                    'jenis_referensi' => 'retur',
                    'id_referensi' => $retur->id_retur,
                    'sudah_dibaca' => false,
                    'created_at' => $waktuSekarang,
                ]);

                return [
                    'refund' => $refund->fresh(['pembayaran', 'percobaanRefund']),
                    'retur' => $retur->fresh(['pesanan']),
                    'percobaan' => $percobaan,
                    'semua_refund_retur_berhasil' => $semuaBerhasil,
                    'seluruh_dana_pesanan_dikembalikan' => $seluruhDanaSudahKembali,
                ];
            });
        } catch (\Throwable $exception) {
            if ($pathBukti) {
                Storage::disk('local')->delete($pathBukti);
            }

            throw $exception;
        }
    }
}
