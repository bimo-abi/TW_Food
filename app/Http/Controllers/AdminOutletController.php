<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdminOutletController extends Controller
{
    /**
     * Menampilkan daftar outlet.
     */
    public function index()
    {
        $outlets = Outlet::orderBy('id_outlet')->get();

        return view('admin.outlet.index', compact('outlets'));
    }


    /**
     * Menampilkan form tambah outlet.
     */
    public function create()
    {
        return view('admin.outlet.create');
    }


    /**
     * Menyimpan outlet baru.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_outlet' => [
                'required',
                'string',
                'max:150',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'nomor_telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'regex:/^-?\d+(\.\d{1,8})?$/',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'regex:/^-?\d+(\.\d{1,8})?$/',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'nama_outlet.required' => 'Nama outlet wajib diisi.',
            'nama_outlet.max' => 'Nama outlet maksimal 150 karakter.',

            'alamat.required' => 'Alamat wajib diisi.',

            'nomor_telepon.max' => 'Nomor telepon maksimal 20 karakter.',

            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus berada antara -90 sampai 90.',
            'latitude.regex' => 'Latitude maksimal memiliki 8 angka di belakang koma.',

            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus berada antara -180 sampai 180.',
            'longitude.regex' => 'Longitude maksimal memiliki 8 angka di belakang koma.',

            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')
                ->store('outlet', 'public');
        }

        try {
            Outlet::create($data);

            return redirect()
                ->route('admin.outlet.index')
                ->with(
                    'success',
                    'Outlet berhasil ditambahkan.'
                );
        } catch (Throwable $e) {
            report($e);

            if (!empty($data['foto'])) {
                Storage::disk('public')->delete($data['foto']);
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Outlet gagal ditambahkan. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Menampilkan form edit outlet.
     */
    public function edit($idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        return view(
            'admin.outlet.edit',
            compact('outlet')
        );
    }


    /**
     * Mengupdate outlet.
     */
    public function update(Request $request, $idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        $data = $request->validate([
            'nama_outlet' => [
                'required',
                'string',
                'max:150',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'nomor_telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'regex:/^-?\d+(\.\d{1,8})?$/',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'regex:/^-?\d+(\.\d{1,8})?$/',
            ],

            'deskripsi' => [
                'nullable',
                'string',
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ], [
            'nama_outlet.required' => 'Nama outlet wajib diisi.',
            'nama_outlet.max' => 'Nama outlet maksimal 150 karakter.',

            'alamat.required' => 'Alamat wajib diisi.',

            'nomor_telepon.max' => 'Nomor telepon maksimal 20 karakter.',

            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus berada antara -90 sampai 90.',
            'latitude.regex' => 'Latitude maksimal memiliki 8 angka di belakang koma.',

            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus berada antara -180 sampai 180.',
            'longitude.regex' => 'Longitude maksimal memiliki 8 angka di belakang koma.',

            'foto.image' => 'File foto harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        if ($request->hasFile('foto')) {
            if ($outlet->foto) {
                Storage::disk('public')->delete($outlet->foto);
            }

            $data['foto'] = $request->file('foto')
                ->store('outlet', 'public');
        }

        try {
            $outlet->update($data);

            return redirect()
                ->route('admin.outlet.index')
                ->with(
                    'success',
                    'Outlet berhasil diperbarui.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Outlet gagal diperbarui. Terjadi kesalahan saat menyimpan data.'
                );
        }
    }


    /**
     * Menghapus outlet.
     */
    public function destroy($idOutlet)
    {
        $outlet = Outlet::findOrFail($idOutlet);

        // Jangan hapus outlet yang sudah digunakan dalam pesanan.
        if ($outlet->pesanan()->exists()) {
            return back()->with(
                'error',
                'Outlet tidak dapat dihapus karena sudah digunakan dalam pesanan.'
            );
        }

        $foto = $outlet->foto;

        try {
            $outlet->delete();

            if ($foto) {
                Storage::disk('public')->delete($foto);
            }

            return redirect()
                ->route('admin.outlet.index')
                ->with(
                    'success',
                    'Outlet berhasil dihapus.'
                );
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'Outlet gagal dihapus. Terjadi kesalahan saat menghapus data.'
            );
        }
    }
}
