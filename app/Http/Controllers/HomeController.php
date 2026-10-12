<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KontenBeranda;
use App\Models\Outlet;

class HomeController extends Controller
{
    public function index()
    {
        // Mengambil produk aktif
        $produk = Produk::where('status_aktif', true)
            ->with('varian')
            ->orderBy('nama_produk')
            ->take(8)
            ->get();

        // Mengambil konten aktif beserta kategorinya
        $konten = KontenBeranda::with('kategori')
            ->where('status_aktif', true)
            ->whereHas('kategori', function ($query) {
                $query->where('status_aktif', true);
            })
            ->orderBy('urutan')
            ->get();

        // Mengambil informasi outlet
        $outlet = Outlet::first();

        // Mengirim data ke halaman Home
        return view('home', compact(
            'produk',
            'konten',
            'outlet'
        ));
    }
}
