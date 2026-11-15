<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PengaturanMitra;
use App\Models\Pesanan;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Transaction;

/**
 * Satu pintu rekonsiliasi status pembayaran Midtrans.
 * Dipakai oleh webhook dan Artisan command agar verifikasi serta dampak
 * bisnis (pesanan, reservasi, riwayat, notifikasi) selalu identik.
 */
class MidtransPaymentReconciliationService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Ambil status resmi dari Midtrans Status API, validasi identitas transaksi,
     * lalu terapkan status lokal secara atomik dan idempoten.
     * $payload hanya diisi oleh webhook asli; command mengirim null.
     */
    public function reconcile(Pembayaran $pembayaran, ?array $payload = null): array
    {
        $pembayaran->refresh();
        $orderId = (string) $pembayaran->id_transaksi;

        if ($orderId === '') {
            return ['status' => 'invalid', 'message' => 'ID transaksi Midtrans tidak tersedia.'];
        }

        // Transaksi yang sudah final tidak perlu dipanggil ulang kecuali refund
        // yang memang perlu disinkronkan melalui status gateway.
        try {
            $statusGateway = (array) Transaction::status($orderId);
        } catch (\Throwable $e) {
            Log::error('Gagal memeriksa status transaksi Midtrans.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $nominalLokal = number_format((float) $pembayaran->jumlah_pembayaran, 2, '.', '');

        // Payload hanya berasal dari webhook asli yang telah lolos validasi signature.
        if ($payload !== null) {
            $grossAmount = $payload['gross_amount'] ?? null;
            if (
                !is_scalar($grossAmount) ||
                number_format((float) $grossAmount, 2, '.', '') !== $nominalLokal
            ) {
                return ['status' => 'invalid', 'message' => 'Nominal transaksi tidak cocok.'];
            }
        }

        $merchantId = config('services.midtrans.merchant_id');
        $gatewayChecks = [
            'order_id' => (string) ($statusGateway['order_id'] ?? '') === $orderId,
            'gross_amount' => number_format((float) ($statusGateway['gross_amount'] ?? 0), 2, '.', '') === $nominalLokal,
            'currency' => (string) ($statusGateway['currency'] ?? '') === 'IDR',
        ];

        if ($merchantId) {
            $gatewayChecks['merchant_id'] = (string) ($statusGateway['merchant_id'] ?? '') === (string) $merchantId;
        }

        if (in_array(false, $gatewayChecks, true)) {
            Log::warning('Validasi transaksi gateway gagal.', [
                'order_id' => $orderId,
                'checks' => $gatewayChecks,
            ]);
            return ['status' => 'invalid', 'message' => 'Data transaksi gateway tidak valid.'];
        }

        // Refund manual dapat tetap menyertai transaction_status=settlement.
        // Sinkronkan daftar refund setiap kali Status API menyediakannya.
        if (!empty($statusGateway['refunds']) && is_array($statusGateway['refunds'])) {
            app(TransaksiIntegrasiService::class)
                ->sinkronkanRefundDariGateway($pembayaran, $statusGateway);
            $pembayaran->refresh();
        }

        $transactionStatus = $statusGateway['transaction_status'] ?? null;
        $fraudStatus = $statusGateway['fraud_status'] ?? null;
        $statusPembayaran = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refund_gateway',
            default => null,
        };

        if ($statusPembayaran === null) {
            Log::warning('Status transaksi Midtrans tidak dikenali.', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
            return ['status' => 'invalid', 'message' => 'Status transaksi belum dapat diverifikasi.'];
        }

        if ($statusPembayaran === 'refund_gateway') {
            app(TransaksiIntegrasiService::class)
                ->sinkronkanRefundDariGateway($pembayaran, $statusGateway);

            return ['status' => 'refund_synced', 'payment_status' => $pembayaran->fresh()->status_pembayaran];
        }

        $diterapkan = DB::transaction(function () use (
            $pembayaran,
            $statusPembayaran,
            $payload,
            $statusGateway
        ) {
            $pembayaran = Pembayaran::where(
                'id_pembayaran',
                $pembayaran->id_pembayaran
            )
                ->lockForUpdate()
                ->firstOrFail();

            $pesanan = Pesanan::where(
                'id_pesanan',
                $pembayaran->id_pesanan
            )
                ->lockForUpdate()
                ->firstOrFail();

            $statusTransaksiSebelumnya =
                $pembayaran->status_pembayaran;

            $statusPesananSebelumnya =
                $pesanan->status_pembayaran;

            /*
             * Idempotensi dan perlindungan status:
             * transaksi refunded tidak diubah oleh webhook pembayaran.
             * Transaksi paid tidak boleh kembali menjadi pending,
             * failed, atau expired karena notifikasi lama.
             */
            if ($statusTransaksiSebelumnya === 'refunded') {
                return false;
            }

            if (
                $statusTransaksiSebelumnya === 'paid' &&
                $statusPembayaran !== 'paid'
            ) {
                return false;
            }

            // Webhook pending yang terlambat tidak boleh menghidupkan
            // kembali transaksi yang sudah gagal/kedaluwarsa. Status paid
            // tetap boleh diterima bila gateway mengonfirmasi pembayaran susulan.
            if (
                in_array($statusTransaksiSebelumnya, ['failed', 'expired'], true) &&
                $statusPembayaran === 'pending'
            ) {
                return false;
            }

            /*
             * Status transaksi diperbarui.
             */
            $pembayaran->status_pembayaran = $statusPembayaran;

            $responsGateway = is_array($pembayaran->respons_gateway)
                ? $pembayaran->respons_gateway
                : [];

            if ($payload !== null) {
                $responsGateway['notifikasi'] = $payload;
            }
            $responsGateway['status_gateway'] = $statusGateway;
            if ($statusPembayaran !== 'pending') {
                unset(
                    $responsGateway['status_pembuatan_transaksi'],
                    $responsGateway['catatan_verifikasi']
                );
            }

            $pembayaran->respons_gateway = $responsGateway;

            if ($statusPembayaran === 'paid') {
                $pembayaran->dibayar_pada =
                    $pembayaran->dibayar_pada ?? now();

                $pembayaran->dikonfirmasi_pada =
                    $pembayaran->dikonfirmasi_pada ?? now();
            }

            $pembayaran->save();

            /*
             * Catat aktivitas jika status transaksi berubah.
             */
            if ($statusTransaksiSebelumnya !== $statusPembayaran) {
                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => null,
                    'jenis_aktor' => 'sistem',
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'jenis_aktivitas' => match ($statusPembayaran) {
                        'paid' => 'pembayaran_berhasil',
                        'failed' => 'pembayaran_gagal',
                        'expired' => 'pembayaran_kadaluarsa',
                        default => 'status_pembayaran_diperbarui',
                    },
                    'status_sebelum' => $statusTransaksiSebelumnya,
                    'status_sesudah' => $statusPembayaran,
                    'deskripsi' => 'Status transaksi pembayaran diperbarui melalui verifikasi Midtrans.',
                    'created_at' => now(),
                ]);
            }

            /*
             * Hitung seluruh transaksi pembayaran yang berhasil.
             * Refund tidak dihitung sebagai pembayaran masuk.
             */
            $totalTerbayar = (int) Pembayaran::where(
                'id_pesanan',
                $pesanan->id_pesanan
            )
                ->whereIn('jenis_pembayaran', [
                    'penuh',
                    'dp',
                    'pelunasan',
                ])
                ->where('status_pembayaran', 'paid')
                ->sum('jumlah_pembayaran');

            /*
             * Tentukan dasar tagihan:
             * - Pesanan DP: gunakan snapshot DP.
             * - Pesanan tanpa DP: gunakan total pesanan.
             */
            $targetTagihan = $pesanan->menggunakan_dp
                ? (int) $pesanan->dasar_perhitungan_dp
                : (int) $pesanan->total_pesanan;

            /*
             * Hitung status pembayaran keseluruhan.
             */
            if (
                $targetTagihan > 0 &&
                $totalTerbayar >= $targetTagihan
            ) {
                $pesanan->status_pembayaran = 'lunas';
            } elseif (
                $pesanan->menggunakan_dp &&
                $totalTerbayar >= (int) $pesanan->nominal_dp
            ) {
                $adaPelunasanBerhasil = Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->where('jenis_pembayaran', 'pelunasan')
                    ->where('status_pembayaran', 'paid')
                    ->exists();

                $pesanan->status_pembayaran = $adaPelunasanBerhasil
                    ? 'sebagian_dibayar'
                    : 'dp_dibayar';
            } elseif ($totalTerbayar > 0) {
                $pesanan->status_pembayaran = 'sebagian_dibayar';
            } else {
                $masihAdaTransaksiPending = Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
                    ->where('status_pembayaran', 'pending')
                    ->exists();

                if ($masihAdaTransaksiPending) {
                    $pesanan->status_pembayaran = 'menunggu_pembayaran';
                } elseif ($statusPembayaran === 'expired') {
                    $pesanan->status_pembayaran = 'kadaluarsa';
                } elseif ($statusPembayaran === 'failed') {
                    $pesanan->status_pembayaran = 'gagal';
                }
            }

            /*
             * Setelah DP atau pembayaran penuh terverifikasi,
             * pesanan mulai diproses.
             */
            if (
                in_array(
                    $pesanan->status_pembayaran,
                    ['dp_dibayar', 'sebagian_dibayar', 'lunas'],
                    true
                ) &&
                $pesanan->status_pesanan === 'menunggu_pembayaran'
            ) {
                $pesanan->status_pesanan = 'sedang_diproses';
            }

            $pesanan->save();

            // Dampak operasional hanya dijalankan setelah pembayaran benar-benar paid.
            // Status reservasi/produksi membuat operasi ini idempoten terhadap webhook ulang.
            $integrasiTransaksi = app(TransaksiIntegrasiService::class);
            if ($statusPembayaran === 'paid') {
                $integrasiTransaksi->terapkanPembayaranBerhasil($pesanan, $pembayaran);
            } elseif (in_array($statusPembayaran, ['failed', 'expired'], true)) {
                $integrasiTransaksi->lepaskanReservasiJikaTidakAdaPembayaranBerhasil(
                    $pesanan,
                    'Seluruh transaksi pembayaran gagal atau kedaluwarsa setelah verifikasi Midtrans.'
                );
            }

            /*
             * Catat perubahan status pembayaran keseluruhan.
             */
            if (
                $statusPesananSebelumnya !==
                $pesanan->status_pembayaran
            ) {
                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => null,
                    'jenis_aktor' => 'sistem',
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'jenis_aktivitas' => 'status_pembayaran_pesanan_diperbarui',
                    'status_sebelum' => $statusPesananSebelumnya,
                    'status_sesudah' => $pesanan->status_pembayaran,
                    'deskripsi' => 'Status pembayaran keseluruhan pesanan diperbarui berdasarkan akumulasi transaksi berhasil.',
                    'created_at' => now(),
                ]);
            }

            /*
             * Buat notifikasi ketika status pembayaran keseluruhan berubah.
             */
            if (
                $statusPesananSebelumnya !==
                $pesanan->status_pembayaran
            ) {
                $pesan = match ($pesanan->status_pembayaran) {
                    'dp_dibayar' =>
                    'Pembayaran DP pesanan ' .
                        $pesanan->nomor_pesanan .
                        ' berhasil diverifikasi. Pesanan mulai diproses.',

                    'sebagian_dibayar' =>
                    'Pembayaran sebagian pesanan ' .
                        $pesanan->nomor_pesanan .
                        ' berhasil diverifikasi.',

                    'lunas' =>
                    'Pembayaran pesanan ' .
                        $pesanan->nomor_pesanan .
                        ' telah lunas dan diverifikasi.',

                    'gagal' =>
                    'Pembayaran pesanan ' .
                        $pesanan->nomor_pesanan .
                        ' gagal.',

                    'kadaluarsa' =>
                    'Transaksi pembayaran pesanan ' .
                        $pesanan->nomor_pesanan .
                        ' telah kedaluwarsa.',

                    default => null,
                };

                if ($pesan !== null) {
                    $dataNotifikasi = [
                        'judul' => 'Pembaruan pembayaran pesanan',
                        'pesan' => $pesan,
                        'jenis' => 'pembayaran',
                        'jenis_referensi' => 'pesanan',
                        'id_referensi' => $pesanan->id_pesanan,
                        'sudah_dibaca' => false,
                        'created_at' => now(),
                    ];

                    /*
                     * Notifikasi untuk pelanggan pemilik pesanan.
                     */
                    Notifikasi::create(
                        array_merge(
                            $dataNotifikasi,
                            [
                                'id_pengguna' => $pesanan->id_pengguna,
                            ]
                        )
                    );

                    /*
                     * Notifikasi untuk mitra aktif.
                     * In-app notification tetap dibuat berdasarkan akun.
                     */
                    $pengaturanMitra = PengaturanMitra::whereHas(
                        'pengguna',
                        function ($query) {
                            $query->where('peran', 'mitra')
                                ->where('status_aktif', true);
                        }
                    )->with('pengguna')->get();

                    foreach ($pengaturanMitra as $pengaturan) {
                        $mitra = $pengaturan->pengguna;

                        if (!$mitra) {
                            continue;
                        }

                        Notifikasi::create(
                            array_merge(
                                $dataNotifikasi,
                                [
                                    'id_pengguna' => $mitra->id_pengguna,
                                ]
                            )
                        );
                    }
                }
            }

            return (
                $statusTransaksiSebelumnya !== $statusPembayaran ||
                $statusPesananSebelumnya !== $pesanan->status_pembayaran
            );
        });

        return [
            'status' => $diterapkan ? 'applied' : 'ignored',
            'payment_status' => $pembayaran->fresh()->status_pembayaran,
            'gateway_status' => $transactionStatus,
        ];
    }
}
