<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar produk aktif
     * beserta varian dan harga aktif.
     */
    public function index()
    {
        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) {
                                $query->where('status_aktif', true);
                            },
                        ]);
                },
            ])
            ->get();

        return response()->json([
            'message' => 'Daftar produk berhasil diambil.',
            'data' => $produk,
        ], 200);
    }

    /**
     * Menampilkan detail satu produk aktif
     * beserta varian dan harga aktif.
     */
    public function show($id)
    {
        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) {
                                $query->where('status_aktif', true);
                            },
                        ]);
                },
            ])
            ->find($id);

        if (!$produk) {
            return response()->json([
                'message' => 'Produk tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail produk berhasil diambil.',
            'data' => $produk,
        ], 200);
    }
}
