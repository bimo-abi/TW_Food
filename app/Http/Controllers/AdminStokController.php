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
                    'max:16777215',
                ],

                'tersedia_pre_order' => [
                    'nullable',
                    'boolean',
                ],

                'tanggal_mulai_pre_order' => [
                    'required_if:tersedia_pre_order,1',
                    'date',
                    'after_or_equal:today',
                ],

                'tanggal_selesai_pre_order' => [
                    'required_if:tersedia_pre_order,1',
                    'date',
                    'after:tanggal_mulai_pre_order',
                ],

                'estimasi_tersedia' => [
                    'required_if:tersedia_pre_order,1',
                    'date',
                    'after:tanggal_selesai_pre_order',
                ],
            ],
            [
                'stok.required' =>
                'Stok wajib diisi.',

                'stok.integer' =>
                'Stok harus berupa angka.',

                'stok.min' =>
                'Stok tidak boleh kurang dari 0.',

                'stok.max' =>
                'Stok maksimal 16.777.215.',

                'tanggal_mulai_pre_order.required_if' =>
                'Tanggal mulai Pre-Order wajib diisi jika Pre-Order diaktifkan.',

                'tanggal_mulai_pre_order.date' =>
                'Tanggal mulai Pre-Order tidak valid.',

                'tanggal_mulai_pre_order.after_or_equal' =>
                'Tanggal mulai Pre-Order tidak boleh sebelum hari ini.',

                'tanggal_selesai_pre_order.required_if' =>
                'Tanggal selesai Pre-Order wajib diisi jika Pre-Order diaktifkan.',

                'tanggal_selesai_pre_order.date' =>
                'Tanggal selesai Pre-Order tidak valid.',

                'tanggal_selesai_pre_order.after' =>
                'Tanggal selesai harus setelah tanggal mulai Pre-Order.',

                'estimasi_tersedia.required_if' =>
                'Estimasi tersedia wajib diisi jika Pre-Order diaktifkan.',

                'estimasi_tersedia.date' =>
                'Estimasi tersedia tidak valid.',

                'estimasi_tersedia.after' =>
                'Estimasi tersedia harus setelah tanggal selesai Pre-Order.',
            ]
        );


        $data['tersedia_pre_order'] =
            $request->boolean('tersedia_pre_order');

        if (!$data['tersedia_pre_order']) {
            $data['tanggal_mulai_pre_order'] = null;
            $data['tanggal_selesai_pre_order'] = null;
            $data['estimasi_tersedia'] = null;
        }

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Stok dan pengaturan pre-order gagal diperbarui. Terjadi kesalahan saat menyimpan perubahan.'
                );
        }
    }
}
