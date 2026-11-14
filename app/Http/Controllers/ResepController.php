<?php

namespace App\Http\Controllers;

use App\Models\Resep;

class ResepController extends Controller
{
    /**
     * Halaman publik daftar resep (bahan dan langkah tampil di kartu).
     */
    public function index()
    {
        $resep = Resep::with('produk.produk')
            ->orderBy('judul')
            ->get();

        return view('resep.index', compact('resep'));
    }
}