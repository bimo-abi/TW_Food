<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Menampilkan daftar produk aktif
     * beserta varian dan harga aktif.
     */
    public function index(Request $request)
    {
        $bolehLihatGrosir = $this->bolehLihatHargaGrosir($request);

        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) use ($bolehLihatGrosir) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) use ($bolehLihatGrosir) {
                                $query->where('status_aktif', true)
                                    ->when(!$bolehLihatGrosir, function ($harga) {
                                        $harga->where('jenis_harga', 'ecer');
                                    });
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
    public function show(Request $request, $id)
    {
        $bolehLihatGrosir = $this->bolehLihatHargaGrosir($request);

        $produk = Produk::query()
            ->where('status_aktif', true)
            ->with([
                'varian' => function ($query) use ($bolehLihatGrosir) {
                    $query
                        ->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($query) use ($bolehLihatGrosir) {
                                $query->where('status_aktif', true)
                                    ->when(!$bolehLihatGrosir, function ($harga) {
                                        $harga->where('jenis_harga', 'ecer');
                                    });
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

    private function bolehLihatHargaGrosir(Request $request): bool
    {
        $pengguna = $request->user('sanctum');

        return $pengguna !== null
            && in_array($pengguna->jenis_pelanggan, ['toko', 'horeca'], true)
            && $pengguna->status_verifikasi === 'disetujui';
    }
}
