<?php

namespace App\Http\Controllers;

use App\Models\VarianProduk;
use Illuminate\Http\Request;

class AdminStokController extends Controller
{
    /**
     * Menampilkan halaman stok dan pre-order.
     */
    public function edit($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        return view(
            'admin.stok.edit',
            compact('varian')
        );
    }


    /**
     * Menyimpan perubahan stok dan pre-order.
     */
    public function update(Request $request, $idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $data = $request->validate([
            'stok' => [
                'required',
                'integer',
                'min:0',
            ],

            'tersedia_pre_order' => [
                'nullable',
                'boolean',
            ],

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
                'admin.stok.edit',
                $varian->id_varian
            )
            ->with(
                'success',
                'Stok dan pengaturan pre-order berhasil diperbarui.'
            );
    }
}
