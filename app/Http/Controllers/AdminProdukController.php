<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProdukController extends Controller
{
    /**
     * Menampilkan daftar produk.
     */
    public function index()
    {
        $produk = Produk::orderBy('nama_produk')->get();

        return view('admin.produk.index', compact('produk'));
    }


    /**
     * Menampilkan form tambah produk.
     */
    public function create()
    {
        return view('admin.produk.create');
    }


    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_produk' => 'required|string|max:150|unique:produk,nama_produk',
            'deskripsi' => 'nullable|string',
            'foto_produk' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['status_aktif'] = true;

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $request
                ->file('foto_produk')
                ->store('products', 'public');
        }

        Produk::create($data);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }


    /**
     * Menampilkan form edit produk.
     */
    public function edit($id)
    {
        $produk = Produk::findOrFail($id);

        return view('admin.produk.edit', compact('produk'));
    }


    /**
     * Mengupdate produk.
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $data = $request->validate([
            'nama_produk' => 'required|string|max:150|unique:produk,nama_produk,' . $produk->id_produk . ',id_produk',
            'deskripsi' => 'nullable|string',
            'foto_produk' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('foto_produk')) {

            if (
                $produk->foto_produk &&
                Storage::disk('public')->exists($produk->foto_produk)
            ) {
                Storage::disk('public')->delete($produk->foto_produk);
            }

            $data['foto_produk'] = $request
                ->file('foto_produk')
                ->store('products', 'public');
        }

        $produk->update($data);

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }


    /**
     * Menghapus produk.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        if (
            $produk->foto_produk &&
            Storage::disk('public')->exists($produk->foto_produk)
        ) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        $produk->delete();

        return redirect()
            ->route('admin.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }


    /**
     * Mengaktifkan / menonaktifkan produk.
     */
    public function toggleStatus($id)
    {
        $produk = Produk::findOrFail($id);

        $produk->update([
            'status_aktif' => !$produk->status_aktif,
        ]);

        return back()->with(
            'success',
            'Status produk berhasil diperbarui.'
        );
    }
}
