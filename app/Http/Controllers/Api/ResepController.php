<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resep;

class ResepController extends Controller
{
    /**
     * Menampilkan daftar resep
     * beserta produk yang digunakan.
     */
    public function index()
    {
        $resep = Resep::query()
            ->with([
                'produk.produk',
            ])
            ->get();

        return response()->json([
            'message' => 'Daftar resep berhasil diambil.',
            'data' => $resep,
        ], 200);
    }

    /**
     * Menampilkan detail satu resep
     * beserta produk yang digunakan.
     */
    public function show($id)
    {
        $resep = Resep::query()
            ->with([
                'produk.produk',
            ])
            ->find($id);

        if (!$resep) {
            return response()->json([
                'message' => 'Resep tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail resep berhasil diambil.',
            'data' => $resep,
        ], 200);
    }
}
