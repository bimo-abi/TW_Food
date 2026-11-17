<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis_pembayaran' => ['nullable', 'in:penuh,dp,pelunasan,refund'],
        ]);

        $tanggalMulai = $filter['tanggal_mulai'] ?? now()->startOfMonth()->toDateString();
        $tanggalSelesai = $filter['tanggal_selesai'] ?? now()->toDateString();

        $query = Pembayaran::query()
            ->with(['pesanan.pengguna'])
            ->where('status_pembayaran', 'paid')
            ->whereBetween('dibayar_pada', [
                $tanggalMulai . ' 00:00:00',
                $tanggalSelesai . ' 23:59:59',
            ]);

        if (!empty($filter['jenis_pembayaran'])) {
            $query->where('jenis_pembayaran', $filter['jenis_pembayaran']);
        }

        $pembayaran = (clone $query)
            ->latest('dibayar_pada')
            ->paginate(20)
            ->withQueryString();

        $totalDiterima = (clone $query)->sum('jumlah_pembayaran');
        $jumlahTransaksi = (clone $query)->count();

        $produkTerlaris = DetailPesanan::query()
            ->select(
                'detail_pesanan.nama_produk_saat_pesan',
                DB::raw('SUM(detail_pesanan.jumlah) as total_terjual')
            )
            ->whereHas('pesanan.pembayaran', function ($pembayaranQuery) use ($tanggalMulai, $tanggalSelesai) {
                $pembayaranQuery
                    ->where('status_pembayaran', 'paid')
                    ->whereBetween('dibayar_pada', [
                        $tanggalMulai . ' 00:00:00',
                        $tanggalSelesai . ' 23:59:59',
                    ]);
            })
            ->groupBy('detail_pesanan.nama_produk_saat_pesan')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        return view('admin.laporan.index', compact(
            'pembayaran',
            'produkTerlaris',
            'totalDiterima',
            'jumlahTransaksi',
            'filter',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }
}
