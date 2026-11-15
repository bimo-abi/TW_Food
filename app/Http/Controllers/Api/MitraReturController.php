<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use Illuminate\Http\Request;

class MitraReturController extends Controller
{
    private function pastikanMitra(Request $request): void
    {
        abort_unless($request->user() && $request->user()->peran === 'mitra' && $request->user()->status_aktif, 403, 'Akses khusus Mitra aktif.');
    }

    public function index(Request $request)
    {
        $this->pastikanMitra($request);

        $query = Retur::query()->with([
            'pesanan:id_pesanan,nomor_pesanan,id_pengguna,status_pesanan,status_pembayaran',
            'pesanan.pengguna:id_pengguna,nama,nomor_telepon',
            'detailRetur', 'buktiRetur',
        ])->latest('diajukan_pada');

        if ($request->filled('status_retur')) {
            $request->validate(['status_retur' => ['in:menunggu_persetujuan,disetujui,menunggu_pengembalian_barang,barang_diterima,sedang_diperiksa,selesai,ditolak,dibatalkan']]);
            $query->where('status_retur', $request->query('status_retur'));
        }

        return response()->json(['message' => 'Daftar pengajuan retur berhasil diambil.', 'data' => $query->paginate(min((int) $request->query('per_page', 20), 50))]);
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
}
