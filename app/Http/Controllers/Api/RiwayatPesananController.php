<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class RiwayatPesananController extends Controller
{
    /** Daftar pesanan milik pelanggan yang sedang login. */
    public function index(Request $request)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan.');

        $query = Pesanan::query()
            ->where('id_pengguna', $pengguna->id_pengguna)
            ->with(['detailPesanan', 'pembayaran', 'retur'])
            ->latest('created_at');

        if ($request->filled('status_pesanan')) {
            $request->validate([
                'status_pesanan' => ['in:menunggu_pembayaran,sedang_diproses,siap_diambil,siap_dikirim,dalam_pengiriman,diterima,dibatalkan'],
            ]);
            $query->where('status_pesanan', $request->string('status_pesanan')->toString());
        }

        return response()->json([
            'message' => 'Riwayat pesanan berhasil diambil.',
            'data' => $query->paginate(min((int) $request->query('per_page', 10), 50)),
        ]);
    }

    /** Detail pesanan dibatasi pada pemilik pesanan. */
    public function show(Request $request, int $id_pesanan)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan.');

        $pesanan = Pesanan::query()
            ->where('id_pengguna', $pengguna->id_pengguna)
            ->where('id_pesanan', $id_pesanan)
            ->with([
                'detailPesanan',
                'pembayaran:id_pembayaran,id_pesanan,jenis_pembayaran,penyedia_pembayaran,metode_pembayaran,jumlah_pembayaran,status_pembayaran,dibayar_pada,waktu_kadaluarsa',
                'retur.detailRetur',
                'retur.buktiRetur',
            ])
            ->firstOrFail();

        return response()->json([
            'message' => 'Detail pesanan berhasil diambil.',
            'data' => $pesanan,
        ]);
    }
}
