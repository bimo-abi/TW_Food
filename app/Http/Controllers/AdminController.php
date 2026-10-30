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
        // Counters
        $totalProduk    = Produk::count();
        $totalVarian    = VarianProduk::count();
        $totalPesanan   = Pesanan::count();
        $pesananBaru    = Pesanan::where('status_pesanan', 'pesanan_diterima')->count();
        $totalStok      = VarianProduk::sum('stok');
        $totalPelanggan = Pengguna::where('peran', 'pelanggan')->count();

        // ===== Stok menipis (stok <= 5) =====
        $stokMenipisQuery = VarianProduk::with('produk')
            ->where('stok', '<=', 5)
            ->orderBy('stok');

        $stokMenipisCount = (clone $stokMenipisQuery)->count();
        $stokMenipisList  = (clone $stokMenipisQuery)->limit(3)->get();

        // Paginated tables, each with its own page parameter
        $produkList = Produk::withCount('varian')
            ->latest()
            ->paginate(5, ['*'], 'produk_page')
            ->withQueryString();

        $varianList = VarianProduk::with([
            'produk',
            'daftarHarga' => function ($q) {
                $q->where('status_aktif', 1)
                    ->orderByRaw("FIELD(jenis_harga, 'grosir', 'ecer')");
            },
        ])
            ->latest()
            ->paginate(5, ['*'], 'varian_page')
            ->withQueryString();

        $stokList = VarianProduk::with('produk')
            ->orderBy('stok')
            ->paginate(5, ['*'], 'stok_page')
            ->withQueryString();

        $pesananList = Pesanan::with('pengguna')
            ->latest('created_at')
            ->paginate(5, ['*'], 'pesanan_page')
            ->withQueryString();

        $pelangganList = Pengguna::where('peran', 'pelanggan')
            ->latest()
            ->paginate(5, ['*'], 'pelanggan_page')
            ->withQueryString();

        return view('admin.dashboard', compact(
            'totalProduk',
            'totalVarian',
            'totalPesanan',
            'pesananBaru',
            'totalStok',
            'totalPelanggan',
            'stokMenipisCount',
            'stokMenipisList',
            'produkList',
            'varianList',
            'stokList',
            'pesananList',
            'pelangganList'
        ));
    }
}
