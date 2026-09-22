<?php

namespace App\Http\Controllers;

use App\Models\DaftarHarga;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDaftarHargaController extends Controller
{
    /**
     * Menampilkan daftar harga dari sebuah varian.
     */
    public function index($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        $harga = DaftarHarga::where(
            'id_varian',
            $idVarian
        )
            ->orderBy('jenis_harga')
            ->get();

        return view('admin.harga.index', compact(
            'varian',
            'harga'
        ));
    }


    /**
     * Form tambah harga.
     */
    public function create($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        return view('admin.harga.create', compact(
            'varian'
        ));
    }


    /**
     * Menyimpan harga baru.
     */
    public function store(Request $request, $idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $data = $request->validate([
            'jenis_harga' => [
                'required',
                'in:ecer,grosir',
                Rule::unique('daftar_harga', 'jenis_harga')
                    ->where(function ($query) use ($idVarian) {
                        return $query->where(
                            'id_varian',
                            $idVarian
                        );
                    }),
            ],

            'harga' => [
                'required',
                'integer',
                'min:0',
            ],

            'minimal_pembelian' => [
                'required',
                'integer',
                'min:1',
            ],

            'satuan_minimal' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $data['id_varian'] = $varian->id_varian;
        $data['status_aktif'] = true;

        DaftarHarga::create($data);

        return redirect()
            ->route(
                'admin.harga.index',
                $varian->id_varian
            )
            ->with(
                'success',
                'Daftar harga berhasil ditambahkan.'
            );
    }


    /**
     * Form edit harga.
     */
    public function edit($idHarga)
    {
        $harga = DaftarHarga::with(
            'varian.produk'
        )->findOrFail($idHarga);

        return view('admin.harga.edit', compact(
            'harga'
        ));
    }


    /**
     * Update harga.
     */
    public function update(Request $request, $idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        $data = $request->validate([
            'jenis_harga' => [
                'required',
                'in:ecer,grosir',

                Rule::unique('daftar_harga', 'jenis_harga')
                    ->where(function ($query) use ($harga) {
                        return $query->where(
                            'id_varian',
                            $harga->id_varian
                        );
                    })
                    ->ignore(
                        $harga->id_daftar_harga,
                        'id_daftar_harga'
                    ),
            ],

            'harga' => [
                'required',
                'integer',
                'min:0',
            ],

            'minimal_pembelian' => [
                'required',
                'integer',
                'min:1',
            ],

            'satuan_minimal' => [
                'nullable',
                'string',
                'max:20',
            ],
        ]);

        $harga->update($data);

        return redirect()
            ->route(
                'admin.harga.index',
                $harga->id_varian
            )
            ->with(
                'success',
                'Daftar harga berhasil diperbarui.'
            );
    }


    /**
     * Hapus harga.
     */
    public function destroy($idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        $idVarian = $harga->id_varian;

        $harga->delete();

        return redirect()
            ->route(
                'admin.harga.index',
                $idVarian
            )
            ->with(
                'success',
                'Daftar harga berhasil dihapus.'
            );
    }


    /**
     * Aktifkan / nonaktifkan harga.
     */
    public function toggleStatus($idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        $harga->update([
            'status_aktif' => !$harga->status_aktif,
        ]);

        return back()->with(
            'success',
            'Status harga berhasil diperbarui.'
        );
    }
}
