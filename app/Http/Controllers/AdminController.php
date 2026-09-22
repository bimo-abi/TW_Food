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
        // Total produk
        $totalProduk = Produk::count();

        // Total varian produk
        $totalVarian = VarianProduk::count();

        // Total pesanan
        $totalPesanan = Pesanan::count();

        // Pesanan baru
        $pesananBaru = Pesanan::where(
            'status_pesanan',
            'pesanan_diterima'
        )->count();

        // Total stok seluruh varian
        $totalStok = VarianProduk::sum('stok');

        // Total pelanggan
        $totalPelanggan = Pengguna::where(
            'peran',
            'pelanggan'
        )->count();

        // 5 pesanan terbaru
        $pesananTerbaru = Pesanan::with('pengguna')
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalProduk',
            'totalVarian',
            'totalPesanan',
            'pesananBaru',
            'totalStok',
            'totalPelanggan',
            'pesananTerbaru'
        ));
    }
}
