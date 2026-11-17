<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Models\Pengguna;
use App\Models\VarianProduk;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $pesananBaru = Pesanan::where('status_pesanan', 'menunggu_pembayaran')->count();

        $pelangganBaru = Pengguna::where('peran', 'pelanggan')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $stokMenipisQuery = VarianProduk::with('produk')
            ->where('stok', '<=', 5)
            ->orderBy('stok');

        $stokMenipisCount = (clone $stokMenipisQuery)->count();
        $stokMenipisList = (clone $stokMenipisQuery)->limit(3)->get();
        $stokList = (clone $stokMenipisQuery)
            ->paginate(5, ['*'], 'stok_page')
            ->withQueryString();

        $pesananList = Pesanan::with('pengguna')
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Hanya pembayaran yang telah diverifikasi sebagai paid yang dihitung.
        $pembayaranHariIni = Pembayaran::query()
            ->where('status_pembayaran', 'paid')
            ->whereDate('dibayar_pada', today())
            ->sum('jumlah_pembayaran');

        $produkTerlaris = DetailPesanan::query()
            ->select('nama_produk_saat_pesan', DB::raw('SUM(detail_pesanan.jumlah) as total_terjual'))
            ->whereHas('pesanan.pembayaran', function ($query) {
                $query->where('status_pembayaran', 'paid');
            })
            ->groupBy('nama_produk_saat_pesan')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $rentangPenjualan = [
            'minggu' => Pembayaran::query()
                ->where('status_pembayaran', 'paid')
                ->whereBetween('dibayar_pada', [now()->startOfWeek(), now()->endOfWeek()])
                ->selectRaw('DATE(dibayar_pada) as label, SUM(jumlah_pembayaran) as total')
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
            'bulan' => Pembayaran::query()
                ->where('status_pembayaran', 'paid')
                ->whereBetween('dibayar_pada', [now()->startOfMonth(), now()->endOfMonth()])
                ->selectRaw('DATE(dibayar_pada) as label, SUM(jumlah_pembayaran) as total')
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
            'tahun' => Pembayaran::query()
                ->where('status_pembayaran', 'paid')
                ->whereYear('dibayar_pada', now()->year)
                ->selectRaw('MONTH(dibayar_pada) as label, SUM(jumlah_pembayaran) as total')
                ->groupBy('label')
                ->orderBy('label')
                ->get(),
        ];

        $salesChart = [
            'minggu' => [
                'labels' => $rentangPenjualan['minggu']->map(fn ($item) => \Carbon\Carbon::parse($item->label)->translatedFormat('D'))->all(),
                'data' => $rentangPenjualan['minggu']->pluck('total')->map(fn ($value) => (int) $value)->all(),
            ],
            'bulan' => [
                'labels' => $rentangPenjualan['bulan']->map(fn ($item) => \Carbon\Carbon::parse($item->label)->format('d M'))->all(),
                'data' => $rentangPenjualan['bulan']->pluck('total')->map(fn ($value) => (int) $value)->all(),
            ],
            'tahun' => [
                'labels' => $rentangPenjualan['tahun']->map(fn ($item) => \Carbon\Carbon::create()->month((int) $item->label)->translatedFormat('M'))->all(),
                'data' => $rentangPenjualan['tahun']->pluck('total')->map(fn ($value) => (int) $value)->all(),
            ],
        ];

        return view('admin.dashboard', compact(
            'pesananBaru',
            'pelangganBaru',
            'stokMenipisCount',
            'stokMenipisList',
            'stokList',
            'pesananList',
            'pembayaranHariIni',
            'produkTerlaris',
            'salesChart'
        ));
    }
}
