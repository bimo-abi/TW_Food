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
            compact(
                'produk',
                'varian'
            )
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
    public function store(
        Request $request,
        $idProduk
    ) {
        $produk = Produk::findOrFail($idProduk);

        // Membersihkan spasi berlebih.
        $request->merge([
            'nama_varian' => is_string($request->nama_varian)
                ? preg_replace(
                    '/\s+/',
                    ' ',
                    trim($request->nama_varian)
                )
                : $request->nama_varian,

            'satuan_jual' => is_string($request->satuan_jual)
                ? preg_replace(
                    '/\s+/',
                    ' ',
                    trim($request->satuan_jual)
                )
                : $request->satuan_jual,
        ]);

        // Validasi data.
        $data = $request->validate(
            [
                'nama_varian' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'regex:/^[\p{L}\p{N}\s]+$/u',

                    Rule::unique(
                        'varian_produk',
                        'nama_varian'
                    )->where(function ($query) use ($idProduk) {
                        return $query->where(
                            'id_produk',
                            $idProduk
                        );
                    }),
                ],

                'berat_gram' => [
                    'nullable',
                    'integer',
                    'min:1',
                    'max:100000',
                ],

                'satuan_jual' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[\p{L}\s]+$/u',
                ],

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
                'nama_varian.required' =>
                'Nama varian wajib diisi.',

                'nama_varian.string' =>
                'Nama varian harus berupa teks.',

                'nama_varian.min' =>
                'Nama varian minimal 1 karakter.',

                'nama_varian.max' =>
                'Nama varian maksimal 100 karakter.',

                'nama_varian.regex' =>
                'Nama varian hanya boleh berisi huruf, angka, dan spasi. Simbol tidak diperbolehkan.',

                'nama_varian.unique' =>
                'Nama varian tersebut sudah digunakan pada produk ini.',

                'berat_gram.integer' =>
                'Berat harus berupa angka bulat.',

                'berat_gram.min' =>
                'Berat minimal 1 gram.',

                'berat_gram.max' =>
                'Berat maksimal 100.000 gram.',

                'satuan_jual.required' =>
                'Satuan jual wajib diisi.',

                'satuan_jual.string' =>
                'Satuan jual harus berupa teks.',

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

        $data['id_produk'] = $produk->id_produk;

        $data['tersedia_pre_order'] =
            $request->boolean('tersedia_pre_order');

        // Jika pre-order tidak aktif, kosongkan tanggal.
        if (!$data['tersedia_pre_order']) {
            $data['tanggal_mulai_pre_order'] = null;
            $data['tanggal_selesai_pre_order'] = null;
            $data['estimasi_tersedia'] = null;
        }

        $data['status_aktif'] = true;

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
                    'Varian produk gagal ditambahkan. Silakan coba lagi.'
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
    public function update(
        Request $request,
        $idVarian
    ) {
        $varian = VarianProduk::findOrFail($idVarian);

        // Membersihkan spasi berlebih.
        $request->merge([
            'nama_varian' => is_string($request->nama_varian)
                ? preg_replace(
                    '/\s+/',
                    ' ',
                    trim($request->nama_varian)
                )
                : $request->nama_varian,

            'satuan_jual' => is_string($request->satuan_jual)
                ? preg_replace(
                    '/\s+/',
                    ' ',
                    trim($request->satuan_jual)
                )
                : $request->satuan_jual,
        ]);

        // Validasi data.
        $data = $request->validate(
            [
                'nama_varian' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'regex:/^[\p{L}\p{N}\s]+$/u',

                    Rule::unique(
                        'varian_produk',
                        'nama_varian'
                    )
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
                    'min:1',
                    'max:100000',
                ],

                'satuan_jual' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[\p{L}\s]+$/u',
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
                'nama_varian.required' =>
                'Nama varian wajib diisi.',

                'nama_varian.string' =>
                'Nama varian harus berupa teks.',

                'nama_varian.min' =>
                'Nama varian minimal 1 karakter.',

                'nama_varian.max' =>
                'Nama varian maksimal 100 karakter.',

                'nama_varian.regex' =>
                'Nama varian hanya boleh berisi huruf, angka, dan spasi. Simbol tidak diperbolehkan.',

                'nama_varian.unique' =>
                'Nama varian tersebut sudah digunakan pada produk ini.',

                'berat_gram.integer' =>
                'Berat harus berupa angka bulat.',

                'berat_gram.min' =>
                'Berat minimal 1 gram.',

                'berat_gram.max' =>
                'Berat maksimal 100.000 gram.',

                'satuan_jual.required' =>
                'Satuan jual wajib diisi.',

                'satuan_jual.string' =>
                'Satuan jual harus berupa teks.',

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

        // Jika pre-order tidak aktif, kosongkan tanggal.
        if (!$data['tersedia_pre_order']) {
            $data['tanggal_mulai_pre_order'] = null;
            $data['tanggal_selesai_pre_order'] = null;
            $data['estimasi_tersedia'] = null;
        }

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
                    'Varian produk gagal diperbarui. Silakan coba lagi.'
                );
        }
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

        // Jangan hapus varian yang masih memiliki daftar harga.
        if ($varian->daftarHarga()->exists()) {
            return back()->with(
                'error',
                'Varian tidak dapat dihapus karena masih memiliki daftar harga.'
            );
        }

        try {
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
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Varian produk gagal dihapus. Silakan coba lagi.'
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
                'Status varian gagal diperbarui. Silakan coba lagi.'
            );
        }
    }
}
