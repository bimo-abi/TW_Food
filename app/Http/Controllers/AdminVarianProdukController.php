<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        return view(
            'admin.varian.index',
            compact('produk', 'varian')
        );
    }


    /**
     * Menampilkan form tambah varian.
     */
    public function create($idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        return view(
            'admin.varian.create',
            compact('produk')
        );
    }


    /**
     * Menyimpan varian baru.
     */
    public function store(Request $request, $idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        $data = $request->validate(
            [
                'nama_varian' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'regex:/^[\pL\pN\s]+$/u',
                    Rule::unique('varian_produk', 'nama_varian')
                        ->where(function ($query) use ($idProduk) {
                            return $query->where(
                                'id_produk',
                                $idProduk
                            );
                        }),
                ],

                'berat_gram' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:65535',
                ],

                'satuan_jual' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[\pL\s]+$/u',
                ],

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
                    'nullable',
                    'date',
                    'after_or_equal:today',
                ],

                'tanggal_selesai_pre_order' => [
                    'nullable',
                    'date',
                    'after:tanggal_mulai_pre_order',
                ],

                'estimasi_tersedia' => [
                    'nullable',
                    'date',
                    'after:tanggal_selesai_pre_order',
                ],
            ],
            [
                'nama_varian.required' =>
                'Nama varian wajib diisi.',

                'nama_varian.min' =>
                'Nama varian minimal 1 karakter.',

                'nama_varian.max' =>
                'Nama varian maksimal 100 karakter.',

                'nama_varian.regex' =>
                'Nama varian hanya boleh berisi huruf, angka, dan spasi.',

                'nama_varian.unique' =>
                'Nama varian tersebut sudah digunakan pada produk ini.',

                'berat_gram.integer' =>
                'Berat harus berupa angka bulat.',

                'berat_gram.min' =>
                'Berat tidak boleh kurang dari 0 gram.',

                'berat_gram.max' =>
                'Berat maksimal 65.535 gram.',

                'satuan_jual.required' =>
                'Satuan jual wajib diisi.',

                'satuan_jual.max' =>
                'Satuan jual maksimal 20 karakter.',

                'satuan_jual.regex' =>
                'Satuan jual hanya boleh berisi huruf dan spasi.',

                'stok.required' =>
                'Stok wajib diisi.',

                'stok.integer' =>
                'Stok harus berupa angka bulat.',

                'stok.min' =>
                'Stok tidak boleh kurang dari 0.',

                'stok.max' =>
                'Stok maksimal 16.777.215.',

                'tanggal_mulai_pre_order.after_or_equal' =>
                'Tanggal mulai pre-order tidak boleh sebelum hari ini.',

                'tanggal_selesai_pre_order.after' =>
                'Tanggal selesai pre-order harus setelah tanggal mulai.',

                'estimasi_tersedia.after' =>
                'Estimasi tersedia harus setelah tanggal selesai pre-order.',
            ]
        );

        $data['id_produk'] = $produk->id_produk;

        $data['tersedia_pre_order'] =
            $request->boolean('tersedia_pre_order');

        $data['status_aktif'] = true;

        if (!$data['tersedia_pre_order']) {
            $data['tanggal_mulai_pre_order'] = null;
            $data['tanggal_selesai_pre_order'] = null;
            $data['estimasi_tersedia'] = null;
        }

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Varian produk gagal ditambahkan. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Menampilkan form edit varian.
     */
    public function edit($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        return view(
            'admin.varian.edit',
            compact('varian')
        );
    }


    /**
     * Mengupdate varian.
     */
    public function update(Request $request, $idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $data = $request->validate(
            [
                'nama_varian' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'regex:/^[\pL\pN\s]+$/u',
                    Rule::unique('varian_produk', 'nama_varian')
                        ->where(function ($query) use ($varian) {
                            return $query->where(
                                'id_produk',
                                $varian->id_produk
                            );
                        })
                        ->ignore(
                            $varian->id_varian,
                            'id_varian'
                        ),
                ],

                'berat_gram' => [
                    'nullable',
                    'integer',
                    'min:0',
                    'max:65535',
                ],

                'satuan_jual' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[\pL\s]+$/u',
                ],
            ],
            [
                'nama_varian.required' =>
                'Nama varian wajib diisi.',

                'nama_varian.min' =>
                'Nama varian minimal 1 karakter.',

                'nama_varian.max' =>
                'Nama varian maksimal 100 karakter.',

                'nama_varian.regex' =>
                'Nama varian hanya boleh berisi huruf, angka, dan spasi.',

                'nama_varian.unique' =>
                'Nama varian tersebut sudah digunakan pada produk ini.',

                'berat_gram.integer' =>
                'Berat harus berupa angka bulat.',

                'berat_gram.min' =>
                'Berat tidak boleh kurang dari 0 gram.',

                'berat_gram.max' =>
                'Berat maksimal 65.535 gram.',

                'satuan_jual.required' =>
                'Satuan jual wajib diisi.',

                'satuan_jual.max' =>
                'Satuan jual maksimal 20 karakter.',

                'satuan_jual.regex' =>
                'Satuan jual hanya boleh berisi huruf dan spasi.',
            ]
        );

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Varian produk gagal diperbarui. Terjadi kesalahan saat menyimpan perubahan.'
                );
        }
    }


    /**
     * Menghapus varian.
     */
    public function destroy($idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        if ($varian->detailPesanan()->exists()) {
            return back()->with(
                'error',
                'Varian tidak dapat dihapus karena sudah digunakan dalam pesanan.'
            );
        }

        $idProduk = $varian->id_produk;

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Varian produk gagal dihapus. Terjadi kesalahan saat menghapus data.'
            );
        }
    }


    /**
     * Mengaktifkan / menonaktifkan varian.
     */
    public function toggleStatus($idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        try {
            $varian->update([
                'status_aktif' => !$varian->status_aktif,
            ]);

            return back()->with(
                'success',
                'Status varian berhasil diperbarui.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Status varian gagal diperbarui. Terjadi kesalahan saat mengubah status.'
            );
        }
    }
}
