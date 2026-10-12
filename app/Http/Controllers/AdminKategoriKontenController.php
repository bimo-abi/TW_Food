<?php

namespace App\Http\Controllers;

use App\Models\KategoriKonten;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminKategoriKontenController extends Controller
{
    public function index()
    {
        $kategori = KategoriKonten::orderBy('nama_kategori')
            ->paginate(10);

        return view('admin.kategori-konten.index', compact('kategori'));
    }

    public function create()
    {
        return view('admin.kategori-konten.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                'unique:kategori_konten,nama_kategori',
            ],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $validated['slug'] = Str::slug($validated['nama_kategori']);
        $validated['status_aktif'] = $request->boolean('status_aktif');

        KategoriKonten::create($validated);

        return redirect()
            ->route('admin.kategori-konten.index')
            ->with('success', 'Kategori konten berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $kategori = KategoriKonten::findOrFail($id);

        return view('admin.kategori-konten.edit', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriKonten::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => [
                'required',
                'string',
                'max:100',
                Rule::unique('kategori_konten', 'nama_kategori')
                    ->ignore($kategori->id_kategori, 'id_kategori'),
            ],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $slugBaru = Str::slug($validated['nama_kategori']);

        $slugSudahDipakai = KategoriKonten::where('slug', $slugBaru)
            ->where('id_kategori', '!=', $kategori->id_kategori)
            ->exists();

        if ($slugSudahDipakai) {
            return back()
                ->withErrors([
                    'nama_kategori' => 'Nama kategori menghasilkan slug yang sudah digunakan.',
                ])
                ->withInput();
        }

        $validated['slug'] = $slugBaru;
        $validated['status_aktif'] = $request->boolean('status_aktif');

        $kategori->update($validated);

        return redirect()
            ->route('admin.kategori-konten.index')
            ->with('success', 'Kategori konten berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = KategoriKonten::findOrFail($id);

        if ($kategori->kontenBeranda()->exists()) {
            return redirect()
                ->route('admin.kategori-konten.index')
                ->with('error', 'Kategori masih digunakan oleh konten. Nonaktifkan kategori jika tidak ingin digunakan lagi.');
        }

        $kategori->delete();

        return redirect()
            ->route('admin.kategori-konten.index')
            ->with('success', 'Kategori konten berhasil dihapus.');
    }
}
