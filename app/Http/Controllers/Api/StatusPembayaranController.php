<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class StatusPembayaranController extends Controller
{
    /** Status pembayaran milik pelanggan; tidak memanggil Midtrans dari aplikasi. */
    public function show(Request $request, int $id_pesanan)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan.');

        $pesanan = Pesanan::query()
            ->where('id_pesanan', $id_pesanan)
            ->where('id_pengguna', $pengguna->id_pengguna)
            ->firstOrFail();

        $pembayaran = Pembayaran::query()
            ->where('id_pesanan', $pesanan->id_pesanan)
            ->whereIn('jenis_pembayaran', ['penuh', 'dp', 'pelunasan'])
            ->orderBy('id_pembayaran')
            ->get([
                'id_pembayaran', 'jenis_pembayaran', 'penyedia_pembayaran',
                'metode_pembayaran', 'jumlah_pembayaran', 'status_pembayaran',
                'dibayar_pada', 'waktu_kadaluarsa',
            ]);

        $totalBerhasil = (int) $pembayaran
            ->where('status_pembayaran', 'paid')
            ->sum('jumlah_pembayaran');

        return response()->json([
            'message' => 'Status pembayaran berhasil diambil.',
            'data' => [
                'id_pesanan' => $pesanan->id_pesanan,
                'nomor_pesanan' => $pesanan->nomor_pesanan,
                'status_pembayaran' => $pesanan->status_pembayaran,
                'total_pesanan' => (int) $pesanan->total_pesanan,
                'total_terbayar' => $totalBerhasil,
                'sisa_pembayaran' => max(0, (int) $pesanan->total_pesanan - $totalBerhasil),
                'pembayaran' => $pembayaran,
            ],
        ]);
    }
}
