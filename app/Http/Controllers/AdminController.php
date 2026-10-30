<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\VarianProduk;
use App\Models\Pesanan;
use App\Models\Pengguna;

class AdminController extends Controller
{
    public function dashboard()
    {
        // ===== KPI =====
        $pesananBaru    = Pesanan::where('status_pesanan', 'pesanan_diterima')->count();
        $pelangganBaru  = Pengguna::where('peran', 'pelanggan')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        // ===== Stok menipis (stok <= 5): dipakai card dan tabel =====
        $stokMenipisQuery = VarianProduk::with('produk')
            ->where('stok', '<=', 5)
            ->orderBy('stok');

        $stokMenipisCount = (clone $stokMenipisQuery)->count();
        $stokMenipisList  = (clone $stokMenipisQuery)->limit(3)->get();
        $stokList         = (clone $stokMenipisQuery)
            ->paginate(5, ['*'], 'stok_page')
            ->withQueryString();

        // ===== Pesanan terbaru (top 5) =====
        $pesananList = Pesanan::with('pengguna')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'pesananBaru',
            'pelangganBaru',
            'stokMenipisCount',
            'stokMenipisList',
            'stokList',
            'pesananList'
        ));
    }
}
