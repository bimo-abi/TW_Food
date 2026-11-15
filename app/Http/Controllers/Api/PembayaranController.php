<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PengaturanMitra;
use App\Models\Pesanan;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
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
            Log::error('Hasil pembuatan transaksi Midtrans belum dapat dipastikan.', [
                'id_transaksi' => $hasil['id_transaksi'],
                'id_pembayaran' => $hasil['pembayaran']->id_pembayaran,
                'jenis_exception' => get_class($e),
                'pesan' => $e->getMessage(),
            ]);

            DB::transaction(function () use ($hasil) {
                $pembayaran = Pembayaran::where(
                    'id_pembayaran',
                    $hasil['pembayaran']->id_pembayaran
                )->lockForUpdate()->first();

                if (!$pembayaran) {
                    return;
                }

                // Jangan menimpa status yang mungkin sudah diperbarui webhook.
                if ($pembayaran->status_pembayaran !== 'pending') {
                    return;
                }

                $responsGateway = (array) $pembayaran->respons_gateway;

                $responsGateway['status_pembuatan_transaksi'] =
                    'perlu_verifikasi';

                $responsGateway['keterangan'] =
                    'Respons pembuatan transaksi belum dapat dipastikan. Status harus diperiksa melalui Midtrans Status API.';

                $pembayaran->respons_gateway = $responsGateway;
                $pembayaran->save();
            });

            return response()->json([
                'message' => 'Status pembuatan transaksi belum dapat dipastikan. Pembayaran sedang menunggu verifikasi.',
                'data' => [
                    'id_pembayaran' => $hasil['pembayaran']->id_pembayaran,
                    'id_transaksi' => $hasil['id_transaksi'],
                    'status_pembayaran' => 'pending',
                    'perlu_verifikasi' => true,
                ],
            ], 503);
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
            !is_string($orderId) ||
            $orderId === '' ||
            !is_scalar($statusCode) ||
            !is_scalar($grossAmount) ||
            !is_string($signatureKey) ||
            $signatureKey === ''
        ) {
            return response()->json([
                'message' => 'Data notifikasi tidak lengkap atau formatnya tidak valid.',
            ], 400);
        }

        /*
         * Validasi signature dari notifikasi Midtrans.
         */
        $serverKey = config('services.midtrans.server_key');

        if (!$serverKey) {
            Log::error('Server key Midtrans belum dikonfigurasi.');

            return response()->json([
                'message' => 'Konfigurasi pembayaran belum tersedia.',
            ], 500);
        }


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
         * Status API dan seluruh dampak bisnis diproses oleh service bersama.
         * Rekonsiliasi Artisan memanggil service yang sama tanpa membuat webhook palsu.
         */
        try {
            $hasilRekonsiliasi = app(\App\Services\MidtransPaymentReconciliationService::class)
                ->reconcile($pembayaran, $payload);
        } catch (Throwable $e) {
            Log::error('Rekonsiliasi pembayaran melalui webhook gagal.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Status transaksi belum dapat diverifikasi.',
            ], 503);
        }

        if ($hasilRekonsiliasi['status'] === 'invalid') {
            return response()->json([
                'message' => $hasilRekonsiliasi['message'],
            ], 422);
        }


        return response()->json([
            'message' => 'Notifikasi pembayaran berhasil diverifikasi dan diproses.',
        ], 200);
    }
}
