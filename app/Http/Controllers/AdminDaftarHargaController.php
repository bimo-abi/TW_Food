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

        return view(
            'admin.harga.index',
            compact('varian', 'harga')
        );
    }


    /**
     * Form tambah harga.
     */
    public function create($idVarian)
    {
        $varian = VarianProduk::with('produk')
            ->findOrFail($idVarian);

        return view(
            'admin.harga.create',
            compact('varian')
        );
    }


    /**
     * Menyimpan harga baru.
     */
    public function store(Request $request, $idVarian)
    {
        $varian = VarianProduk::findOrFail($idVarian);

        $data = $request->validate(
            [
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
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:100',
                    'max:16777215',
                ],

                'minimal_pembelian' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:1',
                    'max:65535',
                ],

                'satuan_minimal' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\pL]+$/u',
                ],
            ],
            [
                'jenis_harga.required' =>
                'Jenis harga wajib dipilih.',

                'jenis_harga.in' =>
                'Jenis harga hanya boleh ecer atau grosir.',

                'jenis_harga.unique' =>
                'Jenis harga tersebut sudah digunakan pada varian ini.',

                'harga.required' =>
                'Harga wajib diisi.',

                'harga.regex' =>
                'Harga hanya boleh berisi angka.',

                'harga.integer' =>
                'Harga harus berupa bilangan bulat.',

                'harga.min' =>
                'Harga minimal Rp100.',

                'harga.max' =>
                'Harga maksimal Rp16.777.215.',

                'minimal_pembelian.required' =>
                'Minimal pembelian wajib diisi.',

                'minimal_pembelian.regex' =>
                'Minimal pembelian hanya boleh berisi angka.',

                'minimal_pembelian.integer' =>
                'Minimal pembelian harus berupa bilangan bulat.',

                'minimal_pembelian.min' =>
                'Minimal pembelian minimal 1.',

                'minimal_pembelian.max' =>
                'Minimal pembelian maksimal 65.535.',

                'satuan_minimal.max' =>
                'Satuan minimal maksimal 20 karakter.',
                'satuan_minimal.regex' => 'Satuan minimal hanya boleh berisi huruf.',
            ]
        );

        $data['id_varian'] = $varian->id_varian;
        $data['status_aktif'] = true;

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Daftar harga gagal ditambahkan. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Form edit harga.
     */
    public function edit($idHarga)
    {
        $harga = DaftarHarga::with(
            'varian.produk'
        )->findOrFail($idHarga);

        return view(
            'admin.harga.edit',
            compact('harga')
        );
    }


    /**
     * Update harga.
     */
    public function update(Request $request, $idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        $data = $request->validate(
            [
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
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:100',
                    'max:16777215',
                ],

                'minimal_pembelian' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:1',
                    'max:65535',
                ],

                'satuan_minimal' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[\pL]+$/u',
                ],
            ],
            [
                'jenis_harga.required' =>
                'Jenis harga wajib dipilih.',

                'jenis_harga.in' =>
                'Jenis harga hanya boleh ecer atau grosir.',

                'jenis_harga.unique' =>
                'Jenis harga tersebut sudah digunakan pada varian ini.',

                'harga.required' =>
                'Harga wajib diisi.',

                'harga.regex' =>
                'Harga hanya boleh berisi angka.',

                'harga.integer' =>
                'Harga harus berupa bilangan bulat.',

                'harga.min' =>
                'Harga minimal Rp100.',

                'harga.max' =>
                'Harga maksimal Rp16.777.215.',

                'minimal_pembelian.required' =>
                'Minimal pembelian wajib diisi.',

                'minimal_pembelian.regex' =>
                'Minimal pembelian hanya boleh berisi angka.',

                'minimal_pembelian.integer' =>
                'Minimal pembelian harus berupa bilangan bulat.',

                'minimal_pembelian.min' =>
                'Minimal pembelian minimal 1.',

                'minimal_pembelian.max' =>
                'Minimal pembelian maksimal 65.535.',

                'satuan_minimal.max' =>
                'Satuan minimal maksimal 20 karakter.',
                'satuan_minimal.regex' => 'Satuan minimal hanya boleh berisi huruf.',
            ]
        );

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Daftar harga gagal diperbarui. Terjadi kesalahan saat menyimpan perubahan.'
                );
        }
    }


    /**
     * Hapus harga.
     */
    public function destroy($idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        $idVarian = $harga->id_varian;

        try {
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
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Daftar harga gagal dihapus. Terjadi kesalahan saat menghapus data.'
            );
        }
    }


    /**
     * Aktifkan / nonaktifkan harga.
     */
    public function toggleStatus($idHarga)
    {
        $harga = DaftarHarga::findOrFail($idHarga);

        try {
            $harga->update([
                'status_aktif' => !$harga->status_aktif,
            ]);

            return back()->with(
                'success',
                'Status harga berhasil diperbarui.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Status harga gagal diperbarui. Terjadi kesalahan saat mengubah status.'
            );
        }
    }
}
