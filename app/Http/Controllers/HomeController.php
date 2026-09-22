<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\KontenBeranda;
use App\Models\Outlet;

class HomeController extends Controller
{
    public function index()
    {
        $produk = Produk::where('status_aktif', true)
            ->with('varian')
            ->orderBy('nama_produk')
            ->take(8)
            ->get();

        $konten = KontenBeranda::where('status_aktif', true)
            ->orderBy('urutan')
            ->get();

        $outlet = Outlet::first();

        return view('home', compact(
            'produk',
            'konten',
            'outlet'
        ));
    }
}
