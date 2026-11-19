<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MitraProdukController extends Controller
{
    private function pastikanMitra(Request $request): void
    {
        abort_unless(
            $request->user()
                && $request->user()->peran === 'mitra'
                && $request->user()->status_aktif,
            403,
            'Akses khusus Mitra aktif.'
        );
    }

    private function relasiProduk()
    {
        return [
            'varian' => function ($query) {
                $query->with([
                    'daftarHarga' => function ($harga) {
                        $harga->orderBy('jenis_harga');
                    },
                ])->orderBy('nama_varian');
            },
        ];
    }

    public function index(Request $request)
    {
        $this->pastikanMitra($request);

        $query = Produk::query()->with($this->relasiProduk())->orderBy('nama_produk');

        if ($request->query('status') === 'aktif') {
            $query->where('status_aktif', true);
        } elseif ($request->query('status') === 'nonaktif') {
            $query->where('status_aktif', false);
        }

        return response()->json([
            'message' => 'Daftar produk Mitra berhasil diambil.',
            'data' => $query->get(),
        ]);
    }

    public function show(Request $request, int $id_produk)
    {
        $this->pastikanMitra($request);

        $produk = Produk::with($this->relasiProduk())->findOrFail($id_produk);

        return response()->json([
            'message' => 'Detail produk Mitra berhasil diambil.',
            'data' => $produk,
        ]);
    }

    public function store(Request $request)
    {
        $this->pastikanMitra($request);
        $this->rapikanNama($request);

        $data = $request->validate([
            'nama_produk' => [
                'required', 'string', 'min:3', 'max:150',
                'regex:/^[\p{L}\s]+$/u', 'unique:produk,nama_produk',
            ],
            'deskripsi' => [
                'nullable', 'string', 'max:5000',
                'regex:/^[\p{L}\p{N}\s.,!?:;\'"()\-+&\/%@#*_]+$/u',
            ],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->pesanValidasi());

        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
        }
        $data['status_aktif'] = true;

        try {
            $produk = Produk::create($data);
        } catch (\Throwable $exception) {
            if (!empty($data['foto_produk'])) {
                Storage::disk('public')->delete($data['foto_produk']);
            }
            report($exception);
            return response()->json(['message' => 'Produk gagal ditambahkan.'], 500);
        }

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => $produk->load($this->relasiProduk()),
        ], 201);
    }

    public function update(Request $request, int $id_produk)
    {
        $this->pastikanMitra($request);
        $this->rapikanNama($request);
        $produk = Produk::findOrFail($id_produk);

        $data = $request->validate([
            'nama_produk' => [
                'required', 'string', 'min:3', 'max:150',
                'regex:/^[\p{L}\s]+$/u',
                Rule::unique('produk', 'nama_produk')->ignore($produk->id_produk, 'id_produk'),
            ],
            'deskripsi' => [
                'nullable', 'string', 'max:5000',
                'regex:/^[\p{L}\p{N}\s.,!?:;\'"()\-+&\/%@#*_]+$/u',
            ],
            'foto_produk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->pesanValidasi());

        $fotoLama = $produk->foto_produk;
        if ($request->hasFile('foto_produk')) {
            $data['foto_produk'] = $request->file('foto_produk')->store('products', 'public');
        }

        try {
            $produk->update($data);
        } catch (\Throwable $exception) {
            if (!empty($data['foto_produk'])) {
                Storage::disk('public')->delete($data['foto_produk']);
            }
            report($exception);
            return response()->json(['message' => 'Produk gagal diperbarui.'], 500);
        }

        if ($request->hasFile('foto_produk') && $fotoLama) {
            Storage::disk('public')->delete($fotoLama);
        }

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => $produk->fresh()->load($this->relasiProduk()),
        ]);
    }

    public function toggleStatus(Request $request, int $id_produk)
    {
        $this->pastikanMitra($request);
        $produk = Produk::findOrFail($id_produk);
        $produk->status_aktif = !$produk->status_aktif;
        $produk->save();

        return response()->json([
            'message' => $produk->status_aktif
                ? 'Produk berhasil diaktifkan.'
                : 'Produk berhasil dinonaktifkan.',
            'data' => $produk->load($this->relasiProduk()),
        ]);
    }

    private function rapikanNama(Request $request): void
    {
        if (is_string($request->input('nama_produk'))) {
            $request->merge([
                'nama_produk' => preg_replace('/\s+/', ' ', trim($request->input('nama_produk'))),
            ]);
        }
    }

    private function pesanValidasi(): array
    {
        return [
            'nama_produk.required' => 'Nama produk wajib diisi.',
            'nama_produk.min' => 'Nama produk minimal 3 karakter.',
            'nama_produk.max' => 'Nama produk maksimal 150 karakter.',
            'nama_produk.regex' => 'Nama produk hanya boleh berisi huruf dan spasi.',
            'nama_produk.unique' => 'Nama produk sudah digunakan.',
            'deskripsi.max' => 'Deskripsi maksimal 5.000 karakter.',
            'deskripsi.regex' => 'Deskripsi mengandung karakter yang tidak diperbolehkan.',
            'foto_produk.image' => 'File yang dipilih harus berupa gambar.',
            'foto_produk.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto_produk.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }
}
