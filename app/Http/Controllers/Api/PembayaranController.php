<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\Pembayaran;
use App\Models\PengaturanMitra;
use App\Models\Pesanan;
use App\Models\RiwayatAktivitasTransaksi;
use App\Services\TransaksiIntegrasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Throwable;

class PembayaranController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Membuat transaksi pembayaran penuh, DP, atau pelunasan.
     */
    public function store(Request $request, int $id_pesanan)
    {
        $validated = $request->validate([
            'jenis_pembayaran' => [
                'required',
                'in:penuh,dp,pelunasan',
            ],
        ]);

        // Kunci dari klien bersifat opsional agar kompatibel dengan
        // aplikasi yang belum mengirim header Idempotency-Key.
        $clientIdempotencyKey = trim((string) $request->header('Idempotency-Key', ''));

        if ($clientIdempotencyKey !== '' && (
            strlen($clientIdempotencyKey) < 8 ||
            strlen($clientIdempotencyKey) > 100
        )) {
            return response()->json([
                'message' => 'Header Idempotency-Key harus berisi 8 sampai 100 karakter.',
            ], 422);
        }

        $pengguna = $request->user();

        if (!$pengguna || !$pengguna->status_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif atau belum login.',
            ], 403);
        }

        /*
         * Validasi dan pencatatan transaksi dilakukan di dalam
         * database transaction agar pesanan terkunci selama proses.
         */
        $hasil = DB::transaction(function () use (
            $id_pesanan,
            $pengguna,
            $validated,
            $clientIdempotencyKey
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

            // Permintaan ulang dengan kunci yang sama mengembalikan
            // transaksi yang sama, bukan membuat transaksi baru.
            if ($clientIdempotencyKey !== '') {
                $transaksiSebelumnya = Pembayaran::where(
                    'kunci_idempotensi',
                    $clientIdempotencyKey
                )->first();

                if ($transaksiSebelumnya) {
                    if ((int) $transaksiSebelumnya->id_pesanan !== (int) $pesanan->id_pesanan) {
                        return [
                            'error' => 'Idempotency-Key sudah digunakan untuk pesanan lain.',
                            'status' => 409,
                        ];
                    }

                    $gatewaySebelumnya = $transaksiSebelumnya->respons_gateway ?? [];

                    if (!empty($gatewaySebelumnya['snap_token'])) {
                        return [
                            'existing' => true,
                            'pembayaran' => $transaksiSebelumnya,
                            'id_transaksi' => $transaksiSebelumnya->id_transaksi,
                            'jumlah_pembayaran' => (int) $transaksiSebelumnya->jumlah_pembayaran,
                            'jenis_pembayaran' => $transaksiSebelumnya->jenis_pembayaran,
                            'snap_token' => $gatewaySebelumnya['snap_token'],
                            'redirect_url' => $gatewaySebelumnya['redirect_url'] ?? null,
                        ];
                    }

                    return [
                        'error' => 'Permintaan dengan Idempotency-Key ini sudah diproses tetapi token pembayaran belum tersedia. Gunakan kunci baru setelah status transaksi diperiksa.',
                        'status' => 409,
                    ];
                }
            }

            if ($pesanan->status_pesanan === 'dibatalkan') {
                return [
                    'error' => 'Pesanan yang dibatalkan tidak dapat dibayar.',
                    'status' => 422,
                ];
            }

            $jenisPembayaran = $validated['jenis_pembayaran'];

            $dasarTagihan = max(
                0,
                (int) $pesanan->subtotal_produk -
                (int) $pesanan->diskon
            );

            if ($dasarTagihan < 1) {
                return [
                    'error' => 'Dasar tagihan pesanan tidak valid.',
                    'status' => 422,
                ];
            }

            $jumlahPembayaran = 0;

            /*
             * BAGIAN A: PELUNASAN
             *
             * Pelunasan hanya dapat dibuat setelah DP berhasil
             * diverifikasi oleh backend.
             */
            if ($jenisPembayaran === 'pelunasan') {
                if (
                    !$pesanan->menggunakan_dp ||
                    !in_array(
                        $pesanan->status_pembayaran,
                        ['dp_dibayar', 'sebagian_dibayar'],
                        true
                    )
                ) {
                    return [
                        'error' => 'Pelunasan hanya dapat dilakukan setelah pembayaran DP berhasil diverifikasi.',
                        'status' => 422,
                    ];
                }

                if (
                    $pesanan->dasar_perhitungan_dp === null ||
                    $pesanan->nominal_dp === null
                ) {
                    return [
                        'error' => 'Snapshot perhitungan DP tidak lengkap.',
                        'status' => 422,
                    ];
                }

                $dasarTagihan = (int) $pesanan->dasar_perhitungan_dp;

                $dpTerbayar = (int) Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->where('jenis_pembayaran', 'dp')
                    ->where('status_pembayaran', 'paid')
                    ->sum('jumlah_pembayaran');

                if ($dpTerbayar < (int) $pesanan->nominal_dp) {
                    return [
                        'error' => 'Pembayaran DP belum memenuhi nominal yang ditetapkan.',
                        'status' => 422,
                    ];
                }

                $pelunasanTerbayar = (int) Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->where('jenis_pembayaran', 'pelunasan')
                    ->where('status_pembayaran', 'paid')
                    ->sum('jumlah_pembayaran');

                $pelunasanPending = Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->where('jenis_pembayaran', 'pelunasan')
                    ->where('status_pembayaran', 'pending')
                    ->exists();

                if ($pelunasanPending) {
                    return [
                        'error' => 'Masih ada transaksi pelunasan yang menunggu pembayaran.',
                        'status' => 409,
                    ];
                }

                $totalTerbayar = $dpTerbayar + $pelunasanTerbayar;

                $jumlahPembayaran = $dasarTagihan - $totalTerbayar;

                if ($jumlahPembayaran < 1) {
                    return [
                        'error' => 'Pesanan sudah lunas atau tidak memiliki sisa tagihan.',
                        'status' => 422,
                    ];
                }
            } else {
                /*
                 * BAGIAN B: PEMBAYARAN PENUH ATAU DP
                 */
                if (!in_array(
                    $pesanan->status_pembayaran,
                    ['belum_dibayar', 'kadaluarsa', 'gagal'],
                    true
                )) {
                    return [
                        'error' => 'Pesanan tidak berada dalam status yang dapat menerima pembayaran penuh atau DP.',
                        'status' => 422,
                    ];
                }

                /*
                 * Cegah pembuatan transaksi baru jika masih ada
                 * transaksi pending atau paid untuk tagihan ini.
                 */
                $pembayaranAktif = Pembayaran::where(
                    'id_pesanan',
                    $pesanan->id_pesanan
                )
                    ->whereIn('status_pembayaran', ['pending', 'paid'])
                    ->exists();

                if ($pembayaranAktif) {
                    return [
                        'error' => 'Pesanan sudah memiliki transaksi pembayaran aktif atau berhasil.',
                        'status' => 409,
                    ];
                }

                if ($jenisPembayaran === 'penuh') {
                    $jumlahPembayaran = (int) $pesanan->total_pesanan;
                }

                /*
                 * BAGIAN C: VALIDASI DAN PERHITUNGAN DP
                 */
                if ($jenisPembayaran === 'dp') {
                    $pelangganDiizinkan = in_array(
                        $pengguna->jenis_pelanggan,
                        ['toko', 'horeca'],
                        true
                    );

                    $pelangganTerverifikasi =
                        $pengguna->status_verifikasi === 'disetujui';

                    if (
                        !$pelangganDiizinkan ||
                        !$pelangganTerverifikasi
                    ) {
                        return [
                            'error' => 'DP hanya tersedia untuk pelanggan Toko atau Horeca yang telah terverifikasi.',
                            'status' => 403,
                        ];
                    }

                    /*
                     * Ambil pengaturan dari mitra aktif.
                     * Tidak memilih pengaturan secara sembarang.
                     */
                    $pengaturanMitra = PengaturanMitra::whereHas(
                        'pengguna',
                        function ($query) {
                            $query->where('peran', 'mitra')
                                ->where('status_aktif', true);
                        }
                    )->get();

                    if ($pengaturanMitra->count() !== 1) {
                        return [
                            'error' => 'Pengaturan DP mitra belum tersedia atau tidak dapat ditentukan.',
                            'status' => 422,
                        ];
                    }

                    $pengaturan = $pengaturanMitra->first();

                    if (!$pengaturan->dp_aktif) {
                        return [
                            'error' => 'Fasilitas DP sedang dinonaktifkan.',
                            'status' => 422,
                        ];
                    }

                    if (
                        $pengaturan->minimal_nominal_dp === null ||
                        (int) $pengaturan->minimal_nominal_dp < 1
                    ) {
                        return [
                            'error' => 'Minimal nominal DP belum dikonfigurasi oleh mitra.',
                            'status' => 422,
                        ];
                    }

                    /*
                     * DP tetap 50% sesuai PRD.
                     * Dasar perhitungan adalah subtotal produk
                     * setelah diskon, tanpa ongkir.
                     */
                    $persentaseDP = 50.00;

                    $nominalDP = (int) round(
                        $dasarTagihan * 0.50
                    );

                    if (
                        $nominalDP <
                        (int) $pengaturan->minimal_nominal_dp
                    ) {
                        return [
                            'error' => 'Nominal DP belum memenuhi batas minimum yang ditentukan mitra.',
                            'status' => 422,
                        ];
                    }

                    if (
                        $nominalDP < 1 ||
                        $nominalDP >= $dasarTagihan
                    ) {
                        return [
                            'error' => 'Nominal DP tidak valid.',
                            'status' => 422,
                        ];
                    }

                    $jumlahPembayaran = $nominalDP;

                    /*
                     * Simpan snapshot DP pada pesanan.
                     */
                    $pesanan->menggunakan_dp = true;
                    $pesanan->dasar_perhitungan_dp = $dasarTagihan;
                    $pesanan->persentase_dp = $persentaseDP;
                    $pesanan->nominal_dp = $nominalDP;
                    $pesanan->save();
                }
            }

            if ($jumlahPembayaran < 1) {
                return [
                    'error' => 'Nominal pembayaran tidak valid.',
                    'status' => 422,
                ];
            }

            /*
             * Buat ID transaksi dan kunci idempotensi unik.
             */
            $idTransaksi = 'TWFOOD-' .
                $pesanan->id_pesanan . '-' .
                Str::upper(Str::random(12));

            $kunciIdempotensi = $clientIdempotencyKey !== ''
                ? $clientIdempotencyKey
                : (string) Str::uuid();

            // Pesanan menunggu pembayaran hanya untuk transaksi awal
            // penuh/DP. Pelunasan tidak boleh menghapus status DP dibayar.
            if (
                $jenisPembayaran !== 'pelunasan' &&
                in_array($pesanan->status_pembayaran, ['belum_dibayar', 'gagal', 'kadaluarsa'], true)
            ) {
                $statusSebelum = $pesanan->status_pembayaran;
                $pesanan->status_pembayaran = 'menunggu_pembayaran';
                $pesanan->save();

                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => $pengguna->id_pengguna,
                    'jenis_aktor' => 'pelanggan',
                    'id_pesanan' => $pesanan->id_pesanan,
                    'id_pembayaran' => null,
                    'jenis_aktivitas' => 'pesanan_menunggu_pembayaran',
                    'status_sebelum' => $statusSebelum,
                    'status_sesudah' => 'menunggu_pembayaran',
                    'deskripsi' => 'Pesanan menunggu verifikasi pembayaran dari gateway.',
                    'created_at' => now(),
                ]);
            }

            $pembayaran = Pembayaran::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'jenis_pembayaran' => $jenisPembayaran,
                'penyedia_pembayaran' => 'Midtrans',
                'id_transaksi' => $idTransaksi,
                'jumlah_pembayaran' => $jumlahPembayaran,
                'status_pembayaran' => 'pending',
                'kunci_idempotensi' => $kunciIdempotensi,
                'waktu_kadaluarsa' => now()->addHours(24),
            ]);

            /*
             * Catat aktivitas pembayaran dimulai.
             */
            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $pengguna->id_pengguna,
                'jenis_aktor' => 'pelanggan',
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => $pembayaran->id_pembayaran,
                'jenis_aktivitas' => 'pembayaran_dimulai',
                'status_sebelum' => null,
                'status_sesudah' => 'pending',
                'deskripsi' => 'Pelanggan membuat transaksi pembayaran ' .
                    $jenisPembayaran . '.',
                'created_at' => now(),
            ]);

            return [
                'pembayaran' => $pembayaran,
                'id_transaksi' => $idTransaksi,
                'jumlah_pembayaran' => $jumlahPembayaran,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'nama_pelanggan' => $pengguna->nama,
                'email_pelanggan' => $pengguna->email,
                'jenis_pembayaran' => $jenisPembayaran,
            ];
        });

        if (isset($hasil['error'])) {
            return response()->json([
                'message' => $hasil['error'],
            ], $hasil['status']);
        }

        if (!empty($hasil['existing'])) {
            return response()->json([
                'message' => 'Permintaan pembayaran sebelumnya ditemukan.',
                'data' => [
                    'id_pembayaran' => $hasil['pembayaran']->id_pembayaran,
                    'id_transaksi' => $hasil['id_transaksi'],
                    'jenis_pembayaran' => $hasil['jenis_pembayaran'],
                    'jumlah_pembayaran' => $hasil['jumlah_pembayaran'],
                    'status_pembayaran' => $hasil['pembayaran']->status_pembayaran,
                    'snap_token' => $hasil['snap_token'],
                    'redirect_url' => $hasil['redirect_url'],
                ],
            ], 200);
        }

        /*
         * Pembuatan transaksi Midtrans dilakukan setelah
         * transaksi database berhasil disimpan.
         */
        try {
            $transaksiMidtrans = Snap::createTransaction([
                'transaction_details' => [
                    'order_id' => $hasil['id_transaksi'],
                    'gross_amount' => $hasil['jumlah_pembayaran'],
                ],
                'item_details' => [
                    [
                        'id' => $hasil['nomor_pesanan'],
                        'price' => $hasil['jumlah_pembayaran'],
                        'quantity' => 1,
                        'name' => 'Pembayaran ' .
                            strtoupper($hasil['jenis_pembayaran']),
                    ],
                ],
                'customer_details' => [
                    'first_name' => $hasil['nama_pelanggan'],
                    'email' => $hasil['email_pelanggan'],
                ],
                // 'expiry' => [
                //     'start_time' => now()->format('Y-m-d H:i:s O'),
                //     'unit' => 'hour',
                //     'duration' => 24,
                // ],
            ]);

            $pembayaran = $hasil['pembayaran'];

            $pembayaran->respons_gateway = [
                'snap_token' => $transaksiMidtrans->token,
                'redirect_url' => $transaksiMidtrans->redirect_url,
            ];

            $pembayaran->save();

            return response()->json([
                'message' => 'Transaksi pembayaran berhasil dibuat.',
                'data' => [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_transaksi' => $hasil['id_transaksi'],
                    'jenis_pembayaran' => $hasil['jenis_pembayaran'],
                    'jumlah_pembayaran' => $hasil['jumlah_pembayaran'],
                    'status_pembayaran' => 'pending',
                    'snap_token' => $transaksiMidtrans->token,
                    'redirect_url' => $transaksiMidtrans->redirect_url,
                ],
            ], 201);
        } catch (Throwable $e) {
            Log::error('Gagal membuat transaksi Midtrans.', [
                'id_transaksi' => $hasil['id_transaksi'],
                'error' => $e->getMessage(),
            ]);

            DB::transaction(function () use ($hasil) {
                $pembayaran = Pembayaran::where(
                    'id_pembayaran',
                    $hasil['pembayaran']->id_pembayaran
                )->lockForUpdate()->first();

                if (!$pembayaran || $pembayaran->status_pembayaran !== 'pending') {
                    return;
                }

                $statusSebelumnya = $pembayaran->status_pembayaran;
                $pembayaran->status_pembayaran = 'failed';
                $pembayaran->respons_gateway = [
                    'error' => 'Gagal membuat transaksi pada gateway.',
                ];
                $pembayaran->save();

                $pesanan = Pesanan::where(
                    'id_pesanan',
                    $pembayaran->id_pesanan
                )->lockForUpdate()->first();

                if ($pesanan) {
                    $masihAdaPembayaranPending = Pembayaran::where(
                        'id_pesanan',
                        $pesanan->id_pesanan
                    )->where('status_pembayaran', 'pending')->exists();

                    $sudahAdaPembayaranBerhasil = Pembayaran::where(
                        'id_pesanan',
                        $pesanan->id_pesanan
                    )->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
                        ->where('status_pembayaran', 'paid')->exists();

                    if (!$masihAdaPembayaranPending && !$sudahAdaPembayaranBerhasil
                        && $pesanan->status_pembayaran === 'menunggu_pembayaran') {
                        $statusPesananSebelumnya = $pesanan->status_pembayaran;
                        $pesanan->status_pembayaran = 'gagal';
                        $pesanan->save();

                        app(TransaksiIntegrasiService::class)
                            ->lepaskanReservasiJikaTidakAdaPembayaranBerhasil(
                                $pesanan,
                                'Pembuatan transaksi Midtrans gagal dan tidak ada transaksi pembayaran aktif atau berhasil.'
                            );

                        RiwayatAktivitasTransaksi::create([
                            'id_pengguna' => null,
                            'jenis_aktor' => 'sistem',
                            'id_pesanan' => $pesanan->id_pesanan,
                            'id_pembayaran' => $pembayaran->id_pembayaran,
                            'jenis_aktivitas' => 'status_pembayaran_pesanan_diperbarui',
                            'status_sebelum' => $statusPesananSebelumnya,
                            'status_sesudah' => 'gagal',
                            'deskripsi' => 'Status pesanan diperbarui karena pembuatan transaksi gateway gagal.',
                            'created_at' => now(),
                        ]);
                    }
                }

                RiwayatAktivitasTransaksi::create([
                    'id_pengguna' => null,
                    'jenis_aktor' => 'sistem',
                    'id_pesanan' => $pembayaran->id_pesanan,
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'jenis_aktivitas' => 'pembuatan_pembayaran_gagal',
                    'status_sebelum' => $statusSebelumnya,
                    'status_sesudah' => 'failed',
                    'deskripsi' => 'Pembuatan transaksi Midtrans gagal.',
                    'created_at' => now(),
                ]);
            });

            return response()->json([
                'message' => 'Transaksi pembayaran gagal dibuat. Silakan coba lagi.',
            ], 502);
        }
    }

    /**
     * Menerima notifikasi Midtrans dan memverifikasi status transaksi.
     */
    public function notification(Request $request)
    {
        $payload = $request->json()->all();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (
            !$orderId ||
            !$statusCode ||
            !$grossAmount ||
            !$signatureKey
        ) {
            return response()->json([
                'message' => 'Data notifikasi tidak lengkap.',
            ], 400);
        }

        /*
         * Validasi signature dari notifikasi Midtrans.
         */
        $serverKey = config('services.midtrans.signature_secret_key');

        if (!$serverKey) {
            Log::error('Server key Midtrans belum dikonfigurasi.');

            return response()->json([
                'message' => 'Konfigurasi pembayaran belum tersedia.',
            ], 500);
        }

        Log::info('Diagnostik signature Midtrans', [
    'order_id' => $orderId,
    'status_code' => $statusCode,
    'gross_amount' => $grossAmount,
    'server_key_terbaca' => !empty($serverKey),
    'server_key_prefix' => substr((string) $serverKey, 0, 12),
    'signature_length' => strlen($signatureKey),
]);
        $expectedSignature = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $serverKey
        );

        if (
            !is_string($signatureKey) ||
            !hash_equals($expectedSignature, $signatureKey)
        ) {
            Log::warning('Signature notifikasi Midtrans tidak valid.', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Signature notifikasi tidak valid.',
            ], 403);
        }

        $pembayaran = Pembayaran::where(
            'id_transaksi',
            $orderId
        )->first();

        if (!$pembayaran) {
            Log::warning('Pembayaran Midtrans tidak ditemukan.', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Transaksi tidak ditemukan.',
            ], 404);
        }

        /*
         * Cocokkan nominal notifikasi dengan nominal transaksi lokal.
         */
        $nominalLokal = number_format(
            (float) $pembayaran->jumlah_pembayaran,
            2,
            '.',
            ''
        );

        $nominalNotifikasi = number_format(
            (float) $grossAmount,
            2,
            '.',
            ''
        );

        if ($nominalLokal !== $nominalNotifikasi) {
            Log::warning('Nominal notifikasi Midtrans tidak cocok.', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Nominal transaksi tidak cocok.',
            ], 422);
        }

        /*
         * Jangan hanya mempercayai payload webhook.
         * Periksa status langsung ke Midtrans Status API.
         */
        try {
            $statusGateway = (array) Transaction::status($orderId);
        } catch (Throwable $e) {
            Log::error('Gagal memeriksa status transaksi Midtrans.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Status transaksi belum dapat diverifikasi.',
            ], 503);
        }

        $merchantId = config('services.midtrans.merchant_id');

        $gatewayChecks = [
            'order_id' =>
                (string) ($statusGateway['order_id'] ?? '') ===
                (string) $orderId,

            'gross_amount' =>
                number_format(
                    (float) ($statusGateway['gross_amount'] ?? 0),
                    2,
                    '.',
                    ''
                ) === $nominalLokal,

            'currency' =>
                (string) ($statusGateway['currency'] ?? '') === 'IDR',
        ];

        /*
         * Merchant ID diperiksa apabila konfigurasi merchant_id
         * tersedia pada config/services.php.
         */
        if ($merchantId) {
            $gatewayChecks['merchant_id'] =
                (string) ($statusGateway['merchant_id'] ?? '') ===
                (string) $merchantId;
        }

        if (in_array(false, $gatewayChecks, true)) {
            Log::warning('Validasi transaksi gateway gagal.', [
                'order_id' => $orderId,
                'checks' => $gatewayChecks,
            ]);

            return response()->json([
                'message' => 'Data transaksi gateway tidak valid.',
            ], 422);
        }

        $transactionStatus =
            $statusGateway['transaction_status'] ?? null;

        $fraudStatus = $statusGateway['fraud_status'] ?? null;

        /*
         * Map status Midtrans ke status transaksi internal.
         */
        $statusPembayaran = match ($transactionStatus) {
            'settlement' => 'paid',

            'capture' => $fraudStatus === 'accept'
                ? 'paid'
                : 'pending',

            'pending' => 'pending',

            'deny', 'cancel' => 'failed',

            'expire' => 'expired',

            /*
             * Refund diproses oleh alur refund tersendiri.
             * Partial refund tidak boleh dianggap sebagai
             * seluruh pembayaran telah dikembalikan.
             */
            'refund', 'partial_refund' => 'refund_gateway',

            default => null,
        };

        if ($statusPembayaran === null) {
            Log::warning('Status transaksi Midtrans tidak dikenali.', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            return response()->json([
                'message' => 'Status transaksi belum dapat diverifikasi.',
            ], 422);
        }

        /*
         * Refund tidak diubah di sini karena membutuhkan pencatatan
         * refund tersendiri dan perhitungan dana yang telah dikembalikan.
         */
        if ($statusPembayaran === 'refund_gateway') {
            app(TransaksiIntegrasiService::class)
                ->sinkronkanRefundDariGateway($pembayaran, $statusGateway);

            Log::info('Notifikasi refund Midtrans diterima dan disinkronkan.', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);

            return response()->json([
                'message' => 'Notifikasi refund diterima dan status refund disinkronkan.',
            ], 200);
        }

        DB::transaction(function () use (
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
                return;
            }

            if (
                $statusTransaksiSebelumnya === 'paid' &&
                $statusPembayaran !== 'paid'
            ) {
                return;
            }

            // Webhook pending yang terlambat tidak boleh menghidupkan
            // kembali transaksi yang sudah gagal/kedaluwarsa. Status paid
            // tetap boleh diterima bila gateway mengonfirmasi pembayaran susulan.
            if (
                in_array($statusTransaksiSebelumnya, ['failed', 'expired'], true) &&
                $statusPembayaran === 'pending'
            ) {
                return;
            }

            /*
             * Status transaksi diperbarui.
             */
            $pembayaran->status_pembayaran = $statusPembayaran;

            $pembayaran->respons_gateway = [
                'notifikasi' => $payload,
                'status_gateway' => $statusGateway,
            ];

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
        });

        return response()->json([
            'message' => 'Notifikasi pembayaran berhasil diverifikasi dan diproses.',
        ], 200);
    }
}
