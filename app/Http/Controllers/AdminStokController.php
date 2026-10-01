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

        $data = $request->validate(
            [
                'stok' => [
                    'required',
                    'integer',
                    'min:0',
                    'max:1000000',
                ],

                'tersedia_pre_order' => [
                    'nullable',
                    'boolean',
                ],

                'tanggal_mulai_pre_order' => [
                    'required_if:tersedia_pre_order,1',
                    'nullable',
                    'date',
                    'after_or_equal:today',
                ],

                'tanggal_selesai_pre_order' => [
                    'required_if:tersedia_pre_order,1',
                    'nullable',
                    'date',
                    'after:tanggal_mulai_pre_order',
                ],

                'estimasi_tersedia' => [
                    'required_if:tersedia_pre_order,1',
                    'nullable',
                    'date',
                    'after:tanggal_selesai_pre_order',
                ],
            ],
            [
                'stok.required' =>
                'Stok wajib diisi.',

                'stok.integer' =>
                'Stok harus berupa angka bulat.',

                'stok.min' =>
                'Stok tidak boleh kurang dari 0.',

                'stok.max' =>
                'Stok maksimal 1.000.000 unit.',

                'tersedia_pre_order.boolean' =>
                'Status pre-order tidak valid.',

                'tanggal_mulai_pre_order.required_if' =>
                'Tanggal mulai pre-order wajib diisi jika pre-order aktif.',

                'tanggal_mulai_pre_order.date' =>
                'Tanggal mulai pre-order tidak valid.',

                'tanggal_mulai_pre_order.after_or_equal' =>
                'Tanggal mulai pre-order tidak boleh sebelum hari ini.',

                'tanggal_selesai_pre_order.required_if' =>
                'Tanggal selesai pre-order wajib diisi jika pre-order aktif.',

                'tanggal_selesai_pre_order.date' =>
                'Tanggal selesai pre-order tidak valid.',

                'tanggal_selesai_pre_order.after' =>
                'Tanggal selesai harus setelah tanggal mulai pre-order.',

                'estimasi_tersedia.required_if' =>
                'Estimasi tersedia wajib diisi jika pre-order aktif.',

                'estimasi_tersedia.date' =>
                'Estimasi tersedia tidak valid.',

                'estimasi_tersedia.after' =>
                'Estimasi tersedia harus setelah tanggal selesai pre-order.',
            ]
        );

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
