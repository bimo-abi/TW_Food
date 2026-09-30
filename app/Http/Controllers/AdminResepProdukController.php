<?php

namespace App\Http\Controllers;

use App\Models\Resep;
use App\Models\Produk;
use App\Models\ResepProduk;
use Illuminate\Http\Request;

class AdminResepProdukController extends Controller
{
    /**
     * Menampilkan produk yang digunakan dalam resep.
     */
    public function index($idResep)
    {
        $resep = Resep::findOrFail($idResep);

        $resepProduk = $resep->produk()
            ->with('produk')
            ->get();

        $produk = Produk::orderBy('nama_produk')->get();

        return view(
            'admin.resep.produk.index',
            compact('resep', 'resepProduk', 'produk')
        );
    }


    /**
     * Menambahkan produk ke resep.
     */
    public function store(Request $request, $idResep)
    {
        $resep = Resep::findOrFail($idResep);

        $data = $request->validate(
            [
                'id_produk' => ['required', 'integer', 'exists:produk,id_produk'],
                'jumlah' => ['required', 'string', 'min:1', 'max:100'],
            ],
            [
                'id_produk.required' => 'Produk wajib dipilih.',
                'id_produk.integer' => 'Produk tidak valid.',
                'id_produk.exists' => 'Produk yang dipilih tidak ditemukan.',
                'jumlah.required' => 'Jumlah wajib diisi.',
                'jumlah.string' => 'Jumlah harus berupa teks.',
                'jumlah.min' => 'Jumlah wajib diisi.',
                'jumlah.max' => 'Jumlah maksimal 100 karakter.',
            ]
        );

        if (
            $resep->produk()
            ->where('id_produk', $data['id_produk'])
            ->exists()
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Produk tersebut sudah digunakan dalam resep ini.'
                );
        }

        try {
            ResepProduk::create([
                'id_resep' => $resep->id_resep,
                'id_produk' => $data['id_produk'],
                'jumlah' => $data['jumlah'],
            ]);

            return back()->with(
                'success',
                'Produk berhasil ditambahkan ke resep.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Produk gagal ditambahkan ke resep.'
                );
        }
    }


    /**
     * Menghapus produk dari resep.
     */
    public function destroy($idResepProduk)
    {
        $resepProduk = ResepProduk::findOrFail($idResepProduk);

        try {
            $resepProduk->delete();

            return back()->with(
                'success',
                'Produk berhasil dihapus dari resep.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Produk gagal dihapus dari resep.'
            );
        }
    }
}
