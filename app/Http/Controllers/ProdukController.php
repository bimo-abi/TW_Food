<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Resep;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Menampilkan katalog produk public.
     * Kalau ada ?resep=ID (dari tombol "Beli bahannya"), hanya produk yang dipakai resep itu yang tampil.
     */
    public function index(Request $request)
    {
        $query = Produk::where('status_aktif', true)
            ->with([
                'varian' => function ($query) {
                    $query->where('status_aktif', true)
                        ->with([
                            'daftarHarga' => function ($harga) {
                                $harga->where('status_aktif', true);
                            }
                        ]);
                }
            ])
            ->orderBy('nama_produk');

        $resepDipilih = null;

        if ($request->filled('resep')) {
            $resepDipilih = Resep::with('produk')->find($request->query('resep'));

            if ($resepDipilih) {
                // 1) Produk yang dihubungkan lewat admin (tabel resep_produk)
                $idProduk = $resepDipilih->produk->pluck('id_produk')->filter()->all();

                // 2) Cadangan: kalau admin belum menghubungkan produk sama sekali,
                //    cari produk yang namanya disebut di daftar bahan resep.
                //    Contoh: bahan "1 sdt Kaldu Jamur Tiram dan Shitake" -> produk
                //    "Kaldu Jamur Tiram dan Shitake".
                $teksBahan = strtolower((string) $resepDipilih->bahan);

                if (empty($idProduk) && $teksBahan !== '') {
                    $semua = Produk::where('status_aktif', true)->get(['id_produk', 'nama_produk']);

                    foreach ($semua as $p) {
                        $nama = strtolower(trim($p->nama_produk));
                        if ($nama !== '' && str_contains($teksBahan, $nama)) {
                            $idProduk[] = $p->id_produk;
                        }
                    }
                }

                $query->whereIn('id_produk', array_unique($idProduk));
            }
        }

        $produk = $query->get();

        return view('produk.index', compact('produk', 'resepDipilih'));
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