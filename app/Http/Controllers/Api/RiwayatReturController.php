<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use Illuminate\Http\Request;

class RiwayatReturController extends Controller
{
    public function index(Request $request)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan.');

        $data = Retur::query()
            ->whereHas('pesanan', fn ($q) => $q->where('id_pengguna', $pengguna->id_pengguna))
            ->with(['pesanan:id_pesanan,nomor_pesanan,status_pesanan', 'detailRetur', 'buktiRetur'])
            ->latest('diajukan_pada')
            ->paginate(min((int) $request->query('per_page', 10), 50));

        return response()->json(['message' => 'Riwayat retur berhasil diambil.', 'data' => $data]);
    }

    public function show(Request $request, int $id_retur)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan.');

        $retur = Retur::query()
            ->where('id_retur', $id_retur)
            ->whereHas('pesanan', fn ($q) => $q->where('id_pengguna', $pengguna->id_pengguna))
            ->with(['pesanan:id_pesanan,nomor_pesanan,status_pesanan', 'detailRetur.detailPesanan', 'buktiRetur', 'refund'])
            ->firstOrFail();

        return response()->json(['message' => 'Detail retur berhasil diambil.', 'data' => $retur]);
    }
}
