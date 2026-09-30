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
            compact(
                'varian',
                'harga'
            )
        );
    }


    /**
     * Menampilkan form tambah harga.
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
    public function store(
        Request $request,
        $idVarian
    ) {
        $varian = VarianProduk::findOrFail($idVarian);

        // Validasi data.
        $data = $request->validate(
            [
                'jenis_harga' => [
                    'required',
                    'in:ecer,grosir',

                    Rule::unique(
                        'daftar_harga',
                        'jenis_harga'
                    )->where(function ($query) use ($idVarian) {
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
                    'max:10000000',
                ],

                'minimal_pembelian' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:1',
                    'max:1000000',
                ],

                'satuan_minimal' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
            ],
            [
                'jenis_harga.required' =>
                'Jenis harga wajib dipilih.',

                'jenis_harga.in' =>
                'Jenis harga hanya boleh ecer atau grosir.',

                'jenis_harga.unique' =>
                'Jenis harga tersebut sudah ada pada varian ini.',

                'harga.required' =>
                'Harga wajib diisi.',

                'harga.regex' =>
                'Harga hanya boleh berisi angka.',

                'harga.integer' =>
                'Harga harus berupa angka bulat.',

                'harga.min' =>
                'Harga minimal Rp100.',

                'harga.max' =>
                'Harga maksimal Rp10.000.000.',

                'minimal_pembelian.required' =>
                'Minimal pembelian wajib diisi.',

                'minimal_pembelian.regex' =>
                'Minimal pembelian hanya boleh berisi angka.',

                'minimal_pembelian.integer' =>
                'Minimal pembelian harus berupa angka bulat.',

                'minimal_pembelian.min' =>
                'Minimal pembelian minimal 1.',

                'minimal_pembelian.max' =>
                'Minimal pembelian maksimal 1.000.000.',

                'satuan_minimal.string' =>
                'Satuan minimal harus berupa teks.',

                'satuan_minimal.max' =>
                'Satuan minimal maksimal 20 karakter.',
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
                    'Daftar harga gagal ditambahkan. Silakan coba lagi.'
                );
        }
    }


    /**
     * Menampilkan form edit harga.
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
     * Mengupdate harga.
     */
    public function update(
        Request $request,
        $idHarga
    ) {
        $harga = DaftarHarga::findOrFail($idHarga);

        // Validasi data.
        $data = $request->validate(
            [
                'jenis_harga' => [
                    'required',
                    'in:ecer,grosir',

                    Rule::unique(
                        'daftar_harga',
                        'jenis_harga'
                    )
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
                    'max:10000000',
                ],

                'minimal_pembelian' => [
                    'required',
                    'regex:/^[0-9]+$/',
                    'integer',
                    'min:1',
                    'max:1000000',
                ],

                'satuan_minimal' => [
                    'nullable',
                    'string',
                    'max:20',
                ],
            ],
            [
                'jenis_harga.required' =>
                'Jenis harga wajib dipilih.',

                'jenis_harga.in' =>
                'Jenis harga hanya boleh ecer atau grosir.',

                'jenis_harga.unique' =>
                'Jenis harga tersebut sudah ada pada varian ini.',

                'harga.required' =>
                'Harga wajib diisi.',

                'harga.regex' =>
                'Harga hanya boleh berisi angka.',

                'harga.integer' =>
                'Harga harus berupa angka bulat.',

                'harga.min' =>
                'Harga minimal Rp100.',

                'harga.max' =>
                'Harga maksimal Rp10.000.000.',

                'minimal_pembelian.required' =>
                'Minimal pembelian wajib diisi.',

                'minimal_pembelian.regex' =>
                'Minimal pembelian hanya boleh berisi angka.',

                'minimal_pembelian.integer' =>
                'Minimal pembelian harus berupa angka bulat.',

                'minimal_pembelian.min' =>
                'Minimal pembelian minimal 1.',

                'minimal_pembelian.max' =>
                'Minimal pembelian maksimal 1.000.000.',

                'satuan_minimal.string' =>
                'Satuan minimal harus berupa teks.',

                'satuan_minimal.max' =>
                'Satuan minimal maksimal 20 karakter.',
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
                    'Daftar harga gagal diperbarui. Silakan coba lagi.'
                );
        }
    }


    /**
     * Menghapus harga.
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
                'Daftar harga gagal dihapus. Silakan coba lagi.'
            );
        }
    }


    /**
     * Mengaktifkan / menonaktifkan harga.
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
                'Status harga gagal diperbarui. Silakan coba lagi.'
            );
        }
    }
}

