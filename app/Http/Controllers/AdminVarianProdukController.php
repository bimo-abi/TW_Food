<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\VarianProduk;
use Illuminate\Http\Request;

class AdminVarianProdukController extends Controller
{
    /**
     * Menampilkan semua varian dari sebuah produk.
     */
    public function index($idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        $varian = VarianProduk::where(
            'id_produk',
            $idProduk
        )
            ->orderBy('nama_varian')
            ->get();

        return view('admin.varian.index', compact(
            'produk',
            'varian'
        ));
    }


    /**
     * Menampilkan form tambah varian.
     */
    public function create($idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        return view('admin.varian.create', compact(
            'produk'
        ));
    }


    /**
     * Menyimpan varian baru.
     */
    public function store(Request $request, $idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        $data = $request->validate([
            'nama_varian' => 'required|string|max:100',
            'berat_gram' => 'nullable|integer|min:0',
            'satuan_jual' => 'required|string|max:20',
            'stok' => 'required|integer|min:0',

            'tersedia_pre_order' => 'nullable|boolean',

            'tanggal_mulai_pre_order' => [
                'nullable',
                'date',
            ],

            'tanggal_selesai_pre_order' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai_pre_order',
            ],

            'estimasi_tersedia' => [
                'nullable',
                'date',
            ],
        ]);

        $data['id_produk'] = $produk->id_produk;

        $data['tersedia_pre_order'] =
            $request->boolean('tersedia_pre_order');

        $data['status_aktif'] = true;

        VarianProduk::create($data);

        return redirect()
            ->route(
                'admin.varian.index',
                $produk->id_produk
            )
            ->with(
                'success',
                'Varian produk berhasil ditambahkan.'
            );
    }


    /**
     * Menampilkan form edit varian.
     */
    public function edit($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        return view('admin.varian.edit', compact(
            'varian'
        ));
    }


    /**
     * Mengupdate varian.
     */
    public function update(Request $request, $idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $data = $request->validate([
            'nama_varian' => 'required|string|max:100',
            'berat_gram' => 'nullable|integer|min:0',
            'satuan_jual' => 'required|string|max:20',
            'stok' => 'required|integer|min:0',

            'tersedia_pre_order' => 'nullable|boolean',

            'tanggal_mulai_pre_order' => [
                'nullable',
                'date',
            ],

            'tanggal_selesai_pre_order' => [
                'nullable',
                'date',
                'after_or_equal:tanggal_mulai_pre_order',
            ],

            'estimasi_tersedia' => [
                'nullable',
                'date',
            ],
        ]);

        $data['tersedia_pre_order'] =
            $request->boolean('tersedia_pre_order');

        $varian->update($data);

        return redirect()
            ->route(
                'admin.varian.index',
                $varian->id_produk
            )
            ->with(
                'success',
                'Varian produk berhasil diperbarui.'
            );
    }


    /**
     * Menghapus varian.
     */
    public function destroy($idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        // Jangan hapus varian yang sudah digunakan dalam pesanan.
        if ($varian->detailPesanan()->exists()) {
            return back()->with(
                'error',
                'Varian tidak dapat dihapus karena sudah digunakan dalam pesanan.'
            );
        }

        $idProduk = $varian->id_produk;

        $varian->delete();

        return redirect()
            ->route(
                'admin.varian.index',
                $idProduk
            )
            ->with(
                'success',
                'Varian produk berhasil dihapus.'
            );
    }


    /**
     * Mengaktifkan / menonaktifkan varian.
     */
    public function toggleStatus($idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $varian->update([
            'status_aktif' => !$varian->status_aktif,
        ]);

        return back()->with(
            'success',
            'Status varian berhasil diperbarui.'
        );
    }
}
