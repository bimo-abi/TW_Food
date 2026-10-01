<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        // Membersihkan spasi berlebih.
        $request->merge([
            'nama_produk' => is_string($request->nama_produk)
                ? preg_replace('/\s+/', ' ', trim($request->nama_produk))
                : $request->nama_produk,
        ]);

        // Validasi data.
        $data = $request->validate(
            [
                'nama_produk' => [
                    'required',
                    'string',
                    'min:3',
                    'max:150',
                    'regex:/^[\p{L}\s]+$/u',
                    'unique:produk,nama_produk',
                ],

                'deskripsi' => [
                    'nullable',
                    'string',
                    'max:5000',
                    'regex:/^[\p{L}\p{N}\s]+$/u',
                ],

                'foto_produk' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],
            ],
            [
                'nama_produk.required' =>
                'Nama produk wajib diisi.',

                'nama_produk.min' =>
                'Nama produk minimal 3 karakter.',

                'nama_produk.max' =>
                'Nama produk maksimal 150 karakter.',

                'nama_produk.regex' =>
                'Nama produk hanya boleh berisi huruf dan spasi. Angka dan simbol tidak diperbolehkan.',

                'nama_produk.unique' =>
                'Nama produk sudah digunakan.',

                'deskripsi.max' =>
                'Deskripsi maksimal 5.000 karakter.',

                'deskripsi.regex' =>
                'Deskripsi hanya boleh berisi huruf, angka, dan spasi. Simbol tidak diperbolehkan.',

                'foto_produk.image' =>
                'File yang dipilih harus berupa gambar.',

                'foto_produk.mimes' =>
                'Format foto harus JPG, JPEG, PNG, atau WEBP.',

                'foto_produk.max' =>
                'Ukuran foto maksimal 2 MB.',
            ]
        );

        $data['status_aktif'] = true;

        try {
            // Simpan foto.
            if ($request->hasFile('foto_produk')) {
                $data['foto_produk'] = $request
                    ->file('foto_produk')
                    ->store('products', 'public');
            }

            // Simpan produk.
            Produk::create($data);

            return redirect()
                ->route('admin.produk.index')
                ->with(
                    'success',
                    'Produk berhasil ditambahkan.'
                );
        } catch (\Throwable $e) {

            // Catat error untuk developer.
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Produk gagal ditambahkan. Silakan coba lagi.'
                );
        }
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

        // Membersihkan spasi berlebih.
        $request->merge([
            'nama_produk' => is_string($request->nama_produk)
                ? preg_replace('/\s+/', ' ', trim($request->nama_produk))
                : $request->nama_produk,
        ]);

        // Validasi data.
        $data = $request->validate(
            [
                'nama_produk' => [
                    'required',
                    'string',
                    'min:3',
                    'max:150',
                    'regex:/^[\p{L}\s]+$/u',
                    Rule::unique('produk', 'nama_produk')
                        ->ignore(
                            $produk->id_produk,
                            'id_produk'
                        ),
                ],
                'deskripsi' => [
                    'nullable',
                    'string',
                    'max:5000',
                    'regex:/^[\p{L}\p{N}\s.\-]+$/u',
                ],

                'foto_produk' => [
                    'nullable',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],
            ],
            [
                'nama_produk.required' =>
                'Nama produk wajib diisi.',

                'nama_produk.min' =>
                'Nama produk minimal 3 karakter.',

                'nama_produk.max' =>
                'Nama produk maksimal 150 karakter.',

                'nama_produk.regex' =>
                'Nama produk hanya boleh berisi huruf dan spasi. Angka dan simbol tidak diperbolehkan.',

                'nama_produk.unique' =>
                'Nama produk sudah digunakan.',

                'deskripsi.max' =>
                'Deskripsi maksimal 5.000 karakter.',

                'deskripsi.regex' =>
                'Deskripsi hanya boleh berisi huruf, angka, dan spasi. Simbol tidak diperbolehkan kecuali (.(titik) dan -(strip)).',

                'foto_produk.image' =>
                'File yang dipilih harus berupa gambar.',

                'foto_produk.mimes' =>
                'Format foto harus JPG, JPEG, PNG, atau WEBP.',

                'foto_produk.max' =>
                'Ukuran foto maksimal 2 MB.',
            ]
        );

        try {
            // Simpan foto lama.
            $fotoLama = $produk->foto_produk;

            // Jika ada foto baru.
            if ($request->hasFile('foto_produk')) {
                $data['foto_produk'] = $request
                    ->file('foto_produk')
                    ->store('products', 'public');
            }

            // Update produk.
            $produk->update($data);

            // Hapus foto lama setelah update berhasil.
            if (
                $request->hasFile('foto_produk') &&
                $fotoLama &&
                Storage::disk('public')->exists($fotoLama)
            ) {
                Storage::disk('public')->delete($fotoLama);
            }

            return redirect()
                ->route('admin.produk.index')
                ->with(
                    'success',
                    'Produk berhasil diperbarui.'
                );
        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Produk gagal diperbarui. Silakan coba lagi.'
                );
        }
    }


    /**
     * Menghapus produk.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        // Produk tidak boleh dihapus jika masih memiliki varian.
        if ($produk->varian()->exists()) {
            return back()->with(
                'error',
                'Produk tidak dapat dihapus karena masih memiliki varian produk.'
            );
        }

        // Produk tidak boleh dihapus jika digunakan dalam resep.
        if ($produk->resep()->exists()) {
            return back()->with(
                'error',
                'Produk tidak dapat dihapus karena masih digunakan dalam resep.'
            );
        }

        try {
            $fotoProduk = $produk->foto_produk;

            // Hapus produk.
            $produk->delete();

            // Hapus foto.
            if (
                $fotoProduk &&
                Storage::disk('public')->exists($fotoProduk)
            ) {
                Storage::disk('public')->delete($fotoProduk);
            }

            return redirect()
                ->route('admin.produk.index')
                ->with(
                    'success',
                    'Produk berhasil dihapus.'
                );
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Produk gagal dihapus. Silakan coba lagi.'
            );
        }
    }


    /**
     * Mengaktifkan / menonaktifkan produk.
     */
    public function toggleStatus($id)
    {
        $produk = Produk::findOrFail($id);

        try {
            $produk->update([
                'status_aktif' => !$produk->status_aktif,
            ]);

            return back()->with(
                'success',
                'Status produk berhasil diperbarui.'
            );
        } catch (\Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Status produk gagal diperbarui. Silakan coba lagi.'
            );
        }
    }
}
