<?php

namespace App\Http\Controllers;
use App\Models\Resep;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function index() {
        $resep = Resep::with('produk')
        ->orderBy('judul')
        ->get();
        return view('resep.index', compact('resep'));
    }
    public function show($id)
    {
        $resep = Resep::with('produk')
            ->findOrFail($id);

        return view('resep.show', compact('resep'));
    }
}
