<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\PengaturanMitra;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;
use Midtrans\Transaction;

class PembayaranController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function store(Request $request, int $id_pesanan)
    {
        $validated = $request->validate([
            'jenis_pembayaran' => [
                'required',
                'in:penuh,dp',
            ],
        ]);

        $pengguna = $request->user();

        if (!$pengguna || !$pengguna->status_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif atau belum login.',
            ], 403);
        }

        // Periksa pesanan dan cegah transaksi pembayaran ganda.
        $hasil = DB::transaction(function () use (
            $id_pesanan,
            $pengguna,
            $validated
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

            if ($pesanan->status_pembayaran !== 'belum_dibayar') {
                return [
                    'error' => 'Pesanan tidak berada dalam status yang dapat dibayar.',
                    'status' => 422,
                ];
            }

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

            $jenisPembayaran = $validated['jenis_pembayaran'];
            $jumlahPembayaran = (int) $pesanan->total_pesanan;

            if ($jumlahPembayaran < 1) {
                return [
                    'error' => 'Total pesanan tidak valid.',
                    'status' => 422,
                ];
            }

            if ($jenisPembayaran === 'dp') {
                if ($pesanan->jenis_pemenuhan !== 'pre_order') {
                    return [
                        'error' => 'DP hanya tersedia untuk pesanan pre-order.',
                        'status' => 422,
                    ];
                }

                // Ambil pengaturan dari akun mitra aktif.
                $pengaturanMitra = PengaturanMitra::whereHas(
                    'pengguna',
                    function ($query) {
                        $query->where('peran', 'mitra')
                            ->where('status_aktif', true);
                    }
                )->get();

                // Jangan memilih pengaturan secara sembarang
                // jika tidak ada atau ada lebih dari satu mitra aktif.
                if ($pengaturanMitra->count() !== 1) {
                    return [
                        'error' => 'Pengaturan mitra belum dapat ditentukan.',
                        'status' => 422,
                    ];
                }

                $pengaturan = $pengaturanMitra->first();

                if (
                    !$pengaturan->dp_aktif ||
                    $pengaturan->minimal_nominal_dp === null ||
                    $pesanan->total_pesanan <
                    $pengaturan->minimal_nominal_dp
                ) {
                    return [
                        'error' => 'DP tidak aktif atau total pesanan belum memenuhi nominal minimum DP.',
                        'status' => 422,
                    ];
                }

                $jumlahPembayaran = (int) round(
                    $pesanan->total_pesanan *
                        ((float) $pengaturan->persentase_dp / 100)
                );

                // DP harus menyisakan nominal untuk pelunasan.
                if (
                    $jumlahPembayaran < 1 ||
                    $jumlahPembayaran >= $pesanan->total_pesanan
                ) {
                    return [
                        'error' => 'Persentase DP tidak menghasilkan nominal DP yang valid.',
                        'status' => 422,
                    ];
                }
            }

            // ID transaksi dibuat unik dan digunakan sebagai order_id Midtrans.
            $idTransaksi = 'TWFOOD-' .
                $pesanan->id_pesanan . '-' .
                Str::upper(Str::random(12));

            $pembayaran = Pembayaran::create([
                'id_pesanan' => $pesanan->id_pesanan,
                'jenis_pembayaran' => $jenisPembayaran,
                'penyedia_pembayaran' => 'Midtrans',
                'id_transaksi' => $idTransaksi,
                'jumlah_pembayaran' => $jumlahPembayaran,
                'status_pembayaran' => 'pending',
            ]);

            return [
                'pembayaran' => $pembayaran,
                'id_transaksi' => $idTransaksi,
                'jumlah_pembayaran' => $jumlahPembayaran,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'nama_pelanggan' => $pengguna->nama,
                'email_pelanggan' => $pengguna->email,
            ];
        });

        if (isset($hasil['error'])) {
            return response()->json([
                'message' => $hasil['error'],
            ], $hasil['status']);
        }

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
                        'name' => $hasil['jenis_pembayaran'] ??
                            $validated['jenis_pembayaran'],
                    ],
                ],
                'customer_details' => [
                    'first_name' => $hasil['nama_pelanggan'],
                    'email' => $hasil['email_pelanggan'],
                ],
            ]);

            $pembayaran = $hasil['pembayaran'];
            $pembayaran->respons_gateway = json_encode([
                'snap_token' => $transaksiMidtrans->token,
                'redirect_url' => $transaksiMidtrans->redirect_url,
            ]);
            $pembayaran->save();

            return response()->json([
                'message' => 'Transaksi pembayaran berhasil dibuat.',
                'data' => [
                    'id_pembayaran' => $pembayaran->id_pembayaran,
                    'id_transaksi' => $hasil['id_transaksi'],
                    'jenis_pembayaran' => $validated['jenis_pembayaran'],
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

            $pembayaran = $hasil['pembayaran'];
            $pembayaran->status_pembayaran = 'failed';
            $pembayaran->respons_gateway = json_encode([
                'error' => 'Gagal membuat transaksi pada gateway.',
            ]);
            $pembayaran->save();

            return response()->json([
                'message' => 'Transaksi pembayaran gagal dibuat. Silakan coba lagi.',
            ], 502);
        }
    }

    public function notification(Request $request)
    {
        $payload = $request->all();

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

        $serverKey = config('services.midtrans.server_key');

        $expectedSignature = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $serverKey
        );

        if (!hash_equals($expectedSignature, $signatureKey)) {
            Log::warning('Signature notifikasi Midtrans tidak valid.', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Signature tidak valid.',
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

        if ((float) $pembayaran->jumlah_pembayaran !== (float) $grossAmount) {
            Log::warning('Nominal notifikasi Midtrans tidak cocok.', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'message' => 'Nominal transaksi tidak cocok.',
            ], 422);
        }

        try {
            $statusGateway = Transaction::status($orderId);
        } catch (Throwable $e) {
            Log::error('Gagal memeriksa status transaksi Midtrans.', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Status transaksi belum dapat diverifikasi.',
            ], 503);
        }

        $statusGateway = (array) $statusGateway;

        if (
            (string) ($statusGateway['order_id'] ?? '') !== (string) $orderId ||
            (float) ($statusGateway['gross_amount'] ?? 0) !== (float) $grossAmount
        ) {
            return response()->json([
                'message' => 'Data transaksi gateway tidak cocok.',
            ], 422);
        }

        $transactionStatus = $statusGateway['transaction_status'] ?? null;
        $fraudStatus = $statusGateway['fraud_status'] ?? null;

        $statusPembayaran = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'pending' => 'pending',
            'deny', 'cancel' => 'failed',
            'expire' => 'expired',
            'refund', 'partial_refund' => 'refunded',
            default => null,
        };

        if ($statusPembayaran === null) {
            return response()->json([
                'message' => 'Status transaksi belum didukung.',
            ], 422);
        }

        DB::transaction(function () use (
            $pembayaran,
            $statusPembayaran,
            $payload
        ) {
            $pembayaran = Pembayaran::where(
                'id_pembayaran',
                $pembayaran->id_pembayaran
            )->lockForUpdate()->firstOrFail();

            $pesanan = Pesanan::where(
                'id_pesanan',
                $pembayaran->id_pesanan
            )->lockForUpdate()->firstOrFail();

            // Abaikan notifikasi jika pembayaran sudah berhasil.
            if ($pembayaran->status_pembayaran === 'paid') {
                return;
            }

            // Jangan mengubah status akhir menjadi pending atau failed
            // hanya karena notifikasi lama datang terlambat.
            if (
                in_array(
                    $pembayaran->status_pembayaran,
                    ['failed', 'expired', 'refunded'],
                    true
                ) &&
                $statusPembayaran === 'pending'
            ) {
                return;
            }

            $pembayaran->status_pembayaran = $statusPembayaran;
            $pembayaran->respons_gateway = json_encode($payload);

            if ($statusPembayaran === 'paid') {
                $pembayaran->dibayar_pada = now();

                if ($pembayaran->jenis_pembayaran === 'penuh') {
                    $pesanan->status_pembayaran = 'lunas';
                } elseif ($pembayaran->jenis_pembayaran === 'dp') {
                    $pesanan->status_pembayaran = 'dp_dibayar';
                }
            } elseif (
                in_array($statusPembayaran, ['failed', 'expired'], true)
            ) {
                $pesanan->status_pembayaran = $statusPembayaran === 'expired'
                    ? 'kadaluarsa'
                    : 'gagal';
            }

            $pembayaran->save();
            $pesanan->save();
        });

        return response()->json([
            'message' => 'Notifikasi pembayaran berhasil diproses.',
        ], 200);
    }
}
