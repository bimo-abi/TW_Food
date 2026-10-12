<?php

namespace App\Http\Controllers;

use App\Models\KontenBeranda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminKontenBerandaController extends Controller
{
    public function index()
    {
        $konten = KontenBeranda::orderBy('urutan')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.konten-beranda.index', compact('konten'));
    }

    public function create()
    {
        return view('admin.konten-beranda.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'jenis_konten' => [
                'required',
                Rule::in([
                    'informasi_produk',
                    'informasi_bahan',
                    'informasi_merek',
                    'acara',
                ]),
            ],
            'urutan' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
                Rule::unique('konten_beranda', 'urutan'),
            ],
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')
                ->store('konten-beranda', 'public');
        }

        KontenBeranda::create($validated);

        return redirect()
            ->route('admin.konten-beranda.index')
            ->with('success', 'Konten Beranda berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $konten = KontenBeranda::findOrFail($id);

        return view('admin.konten-beranda.edit', compact('konten'));
    }

    public function update(Request $request, $id)
    {
        $konten = KontenBeranda::findOrFail($id);

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'jenis_konten' => [
                'required',
                Rule::in([
                    'informasi_produk',
                    'informasi_bahan',
                    'informasi_merek',
                    'acara',
                ]),
            ],
            'urutan' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
                Rule::unique('konten_beranda', 'urutan')
                    ->ignore($konten->id_konten, 'id_konten'),
            ],
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif');

        $gambarLama = $konten->gambar;

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')
                ->store('konten-beranda', 'public');
        } else {
            unset($validated['gambar']);
        }

        $konten->update($validated);

        if (
            $request->hasFile('gambar') &&
            $gambarLama
        ) {
            Storage::disk('public')->delete($gambarLama);
        }

        return redirect()
            ->route('admin.konten-beranda.index')
            ->with('success', 'Konten Beranda berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $konten = KontenBeranda::findOrFail($id);

        if ($konten->gambar) {
            Storage::disk('public')->delete($konten->gambar);
        }

        $konten->delete();

        return redirect()
            ->route('admin.konten-beranda.index')
            ->with('success', 'Konten Beranda berhasil dihapus.');
    }
}
