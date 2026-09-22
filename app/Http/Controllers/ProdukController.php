<?php

namespace App\Http\Controllers;

use App\Models\Produk;

class ProdukController extends Controller
{
    /**
     * Menampilkan katalog produk public.
     */
    public function index()
    {
        $produk = Produk::where('status_aktif', true)
            ->with([
                'varian' => function ($query) {
                    $query->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($harga) {
                                $harga->where('jenis_harga', 'ecer')
                                    ->where('status_aktif', true);
                            }
                        ]);
                }
            ])
            ->orderBy('nama_produk')
            ->get();

        return view('produk.index', compact('produk'));
    }

    /**
     * Method create tidak digunakan untuk public website.
     * CRUD produk dilakukan melalui admin.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Method store tidak digunakan untuk public website.
     * Penambahan produk dilakukan melalui admin.
     */
    public function store()
    {
        abort(404);
    }

    /**
     * Method edit tidak digunakan untuk public website.
     * Edit produk dilakukan melalui admin.
     */
    public function edit($id)
    {
        abort(404);
    }

    /**
     * Method update tidak digunakan untuk public website.
     * Update produk dilakukan melalui admin.
     */
    public function update($id)
    {
        abort(404);
    }

    /**
     * Method destroy tidak digunakan untuk public website.
     * Penghapusan produk dilakukan melalui admin.
     */
    public function destroy($id)
    {
        abort(404);
    }
}
