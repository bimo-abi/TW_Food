<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DaftarHarga;
use App\Models\Produk;
use App\Models\ReservasiStok;
use Illuminate\Support\Facades\DB;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MitraVarianProdukController extends Controller
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

    private function muatVarian(VarianProduk $varian): VarianProduk
    {
        return $varian->fresh()->load([
            'produk:id_produk,nama_produk',
            'daftarHarga' => fn ($query) => $query->orderBy('jenis_harga'),
        ]);
    }

    public function index(Request $request, int $id_produk)
    {
        $this->pastikanMitra($request);
        $produk = Produk::findOrFail($id_produk);
        $varian = VarianProduk::where('id_produk', $produk->id_produk)
            ->with(['daftarHarga' => fn ($query) => $query->orderBy('jenis_harga')])
            ->orderBy('nama_varian')
            ->get();

        return response()->json([
            'message' => 'Daftar varian produk berhasil diambil.',
            'data' => $varian,
        ]);
    }

    public function store(Request $request, int $id_produk)
    {
        $this->pastikanMitra($request);
        $produk = Produk::findOrFail($id_produk);
        $this->rapikanNamaVarian($request);

        $data = $request->validate([
            'nama_varian' => [
                'required', 'string', 'min:1', 'max:100',
                'regex:/^[\pL\pN\s]+$/u',
                Rule::unique('varian_produk', 'nama_varian')->where(
                    fn ($query) => $query->where('id_produk', $produk->id_produk)
                ),
            ],
            'berat_gram' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'satuan_jual' => ['required', 'string', 'max:20', 'regex:/^[\pL\s]+$/u'],
        ], [
            'nama_varian.required' => 'Nama varian wajib diisi.',
            'nama_varian.regex' => 'Nama varian hanya boleh berisi huruf, angka, dan spasi.',
            'nama_varian.unique' => 'Nama varian tersebut sudah digunakan pada produk ini.',
            'berat_gram.integer' => 'Berat harus berupa angka bulat.',
            'berat_gram.min' => 'Berat tidak boleh kurang dari 0 gram.',
            'berat_gram.max' => 'Berat maksimal 65.535 gram.',
            'satuan_jual.required' => 'Satuan jual wajib diisi.',
            'satuan_jual.regex' => 'Satuan jual hanya boleh berisi huruf dan spasi.',
        ]);

        $data['id_produk'] = $produk->id_produk;
        $data['stok'] = 0;
        $data['tersedia_pre_order'] = false;
        $data['status_aktif'] = true;
        $varian = VarianProduk::create($data);

        return response()->json([
            'message' => 'Varian produk berhasil ditambahkan.',
            'data' => $this->muatVarian($varian),
        ], 201);
    }

    public function update(Request $request, int $id_varian)
    {
        $this->pastikanMitra($request);
        $varian = VarianProduk::findOrFail($id_varian);
        $this->rapikanNamaVarian($request);

        $data = $request->validate([
            'nama_varian' => [
                'required', 'string', 'min:1', 'max:100',
                'regex:/^[\pL\pN\s]+$/u',
                Rule::unique('varian_produk', 'nama_varian')
                    ->where(fn ($query) => $query->where('id_produk', $varian->id_produk))
                    ->ignore($varian->id_varian, 'id_varian'),
            ],
            'berat_gram' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'satuan_jual' => ['required', 'string', 'max:20', 'regex:/^[\pL\s]+$/u'],
        ], [
            'nama_varian.required' => 'Nama varian wajib diisi.',
            'nama_varian.regex' => 'Nama varian hanya boleh berisi huruf, angka, dan spasi.',
            'nama_varian.unique' => 'Nama varian tersebut sudah digunakan pada produk ini.',
            'berat_gram.integer' => 'Berat harus berupa angka bulat.',
            'berat_gram.min' => 'Berat tidak boleh kurang dari 0 gram.',
            'berat_gram.max' => 'Berat maksimal 65.535 gram.',
            'satuan_jual.required' => 'Satuan jual wajib diisi.',
            'satuan_jual.regex' => 'Satuan jual hanya boleh berisi huruf dan spasi.',
        ]);

        $varian->update($data);
        return response()->json([
            'message' => 'Varian produk berhasil diperbarui.',
            'data' => $this->muatVarian($varian),
        ]);
    }

    public function toggleStatusVarian(Request $request, int $id_varian)
    {
        $this->pastikanMitra($request);
        $varian = VarianProduk::findOrFail($id_varian);
        $varian->status_aktif = !$varian->status_aktif;
        $varian->save();

        return response()->json([
            'message' => $varian->status_aktif ? 'Varian berhasil diaktifkan.' : 'Varian berhasil dinonaktifkan.',
            'data' => $this->muatVarian($varian),
        ]);
    }

    public function storeHarga(Request $request, int $id_varian)
    {
        $this->pastikanMitra($request);
        $varian = VarianProduk::findOrFail($id_varian);
        $data = $this->validasiHarga($request, $varian);
        $data['id_varian'] = $varian->id_varian;
        $data['status_aktif'] = true;
        $harga = DaftarHarga::create($data);

        return response()->json([
            'message' => 'Daftar harga berhasil ditambahkan.',
            'data' => $harga,
        ], 201);
    }

    public function updateHarga(Request $request, int $id_daftar_harga)
    {
        $this->pastikanMitra($request);
        $harga = DaftarHarga::findOrFail($id_daftar_harga);
        $data = $this->validasiHarga($request, $harga->varian, $harga);
        $harga->update($data);

        return response()->json([
            'message' => 'Daftar harga berhasil diperbarui.',
            'data' => $harga->fresh(),
        ]);
    }

    public function toggleStatusHarga(Request $request, int $id_daftar_harga)
    {
        $this->pastikanMitra($request);
        $harga = DaftarHarga::findOrFail($id_daftar_harga);
        $harga->status_aktif = !$harga->status_aktif;
        $harga->save();

        return response()->json([
            'message' => $harga->status_aktif ? 'Harga berhasil diaktifkan.' : 'Harga berhasil dinonaktifkan.',
            'data' => $harga,
        ]);
    }

    /**
     * Memperbarui stok fisik varian. Stok tidak boleh lebih kecil
     * daripada jumlah reservasi pesanan yang masih aktif dan belum kedaluwarsa.
     */
    public function updateStok(Request $request, int $id_varian)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'stok' => ['required', 'regex:/^[0-9]+$/', 'integer', 'min:0', 'max:16777215'],
        ], [
            'stok.required' => 'Stok wajib diisi.',
            'stok.regex' => 'Stok hanya boleh berisi angka bulat.',
            'stok.min' => 'Stok tidak boleh kurang dari 0.',
            'stok.max' => 'Stok maksimal 16.777.215.',
        ]);

        $result = DB::transaction(function () use ($id_varian, $data) {
            $varian = VarianProduk::where('id_varian', $id_varian)
                ->lockForUpdate()
                ->firstOrFail();

            $reservasiAktif = (int) ReservasiStok::where('id_varian', $varian->id_varian)
                ->where('status_reservasi', 'aktif')
                ->where('kadaluarsa_pada', '>', now())
                ->sum('jumlah');

            $stokBaru = (int) $data['stok'];
            if ($stokBaru < $reservasiAktif) {
                abort(response()->json([
                    'message' => 'Stok tidak dapat diatur lebih rendah daripada jumlah yang sedang direservasi.',
                    'errors' => [
                        'stok' => ['Minimal stok fisik saat ini adalah ' . $reservasiAktif . ' unit karena terdapat reservasi aktif.'],
                    ],
                    'data' => [
                        'stok_sekarang' => (int) $varian->stok,
                        'jumlah_reservasi_aktif' => $reservasiAktif,
                        'stok_minimal' => $reservasiAktif,
                    ],
                ], 422));
            }

            $varian->stok = $stokBaru;
            $varian->save();

            return [
                'varian' => $varian->fresh()->load('daftarHarga'),
                'jumlah_reservasi_aktif' => $reservasiAktif,
                'stok_tersedia' => $stokBaru - $reservasiAktif,
            ];
        });

        return response()->json([
            'message' => 'Stok varian berhasil diperbarui.',
            'data' => $result['varian'],
            'meta' => [
                'stok_fisik' => (int) $result['varian']->stok,
                'jumlah_reservasi_aktif' => $result['jumlah_reservasi_aktif'],
                'stok_tersedia' => $result['stok_tersedia'],
            ],
        ]);
    }


    /**
     * Mengatur ketersediaan dan periode pre-order per varian.
     * Saat dinonaktifkan, seluruh tanggal pre-order dikosongkan.
     */
    public function updatePreOrder(Request $request, int $id_varian)
    {
        $this->pastikanMitra($request);

        $varian = VarianProduk::findOrFail($id_varian);
        $aktif = $request->boolean('tersedia_pre_order');

        if (!$aktif) {
            $varian->update([
                'tersedia_pre_order' => false,
                'tanggal_mulai_pre_order' => null,
                'tanggal_selesai_pre_order' => null,
                'estimasi_tersedia' => null,
            ]);

            return response()->json([
                'message' => 'Pre-order berhasil dinonaktifkan.',
                'data' => $this->muatVarian($varian),
            ]);
        }

        $data = $request->validate([
            'tersedia_pre_order' => ['required', 'boolean'],
            'tanggal_mulai_pre_order' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai_pre_order' => ['required', 'date', 'after:tanggal_mulai_pre_order'],
            'estimasi_tersedia' => ['required', 'date', 'after:tanggal_selesai_pre_order'],
        ], [
            'tanggal_mulai_pre_order.required' => 'Tanggal mulai pre-order wajib diisi.',
            'tanggal_mulai_pre_order.date' => 'Tanggal mulai pre-order tidak valid.',
            'tanggal_mulai_pre_order.after_or_equal' => 'Tanggal mulai pre-order tidak boleh sebelum hari ini.',
            'tanggal_selesai_pre_order.required' => 'Tanggal selesai pre-order wajib diisi.',
            'tanggal_selesai_pre_order.date' => 'Tanggal selesai pre-order tidak valid.',
            'tanggal_selesai_pre_order.after' => 'Tanggal selesai harus setelah tanggal mulai pre-order.',
            'estimasi_tersedia.required' => 'Estimasi tersedia wajib diisi.',
            'estimasi_tersedia.date' => 'Estimasi tersedia tidak valid.',
            'estimasi_tersedia.after' => 'Estimasi tersedia harus setelah tanggal selesai pre-order.',
        ]);

        $varian->update([
            'tersedia_pre_order' => true,
            'tanggal_mulai_pre_order' => $data['tanggal_mulai_pre_order'],
            'tanggal_selesai_pre_order' => $data['tanggal_selesai_pre_order'],
            'estimasi_tersedia' => $data['estimasi_tersedia'],
        ]);

        return response()->json([
            'message' => 'Pengaturan pre-order berhasil diperbarui.',
            'data' => $this->muatVarian($varian),
        ]);
    }

    private function validasiHarga(Request $request, VarianProduk $varian, ?DaftarHarga $hargaLama = null): array
    {
        $unique = Rule::unique('daftar_harga', 'jenis_harga')
            ->where(fn ($query) => $query->where('id_varian', $varian->id_varian));
        if ($hargaLama) {
            $unique->ignore($hargaLama->id_daftar_harga, 'id_daftar_harga');
        }

        return $request->validate([
            'jenis_harga' => ['required', 'in:ecer,grosir', $unique],
            'harga' => ['required', 'regex:/^[0-9]+$/', 'integer', 'min:100', 'max:16777215'],
            'minimal_pembelian' => ['required', 'regex:/^[0-9]+$/', 'integer', 'min:1', 'max:65535'],
            'satuan_minimal' => ['nullable', 'string', 'max:20', 'regex:/^[\pL]+$/u'],
        ], [
            'jenis_harga.required' => 'Jenis harga wajib dipilih.',
            'jenis_harga.in' => 'Jenis harga hanya boleh ecer atau grosir.',
            'jenis_harga.unique' => 'Jenis harga tersebut sudah digunakan pada varian ini.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.regex' => 'Harga hanya boleh berisi angka.',
            'harga.min' => 'Harga minimal Rp100.',
            'harga.max' => 'Harga maksimal Rp16.777.215.',
            'minimal_pembelian.required' => 'Minimal pembelian wajib diisi.',
            'minimal_pembelian.regex' => 'Minimal pembelian hanya boleh berisi angka.',
            'minimal_pembelian.min' => 'Minimal pembelian minimal 1.',
            'minimal_pembelian.max' => 'Minimal pembelian maksimal 65.535.',
            'satuan_minimal.max' => 'Satuan minimal maksimal 20 karakter.',
            'satuan_minimal.regex' => 'Satuan minimal hanya boleh berisi huruf.',
        ]);
    }

    private function rapikanNamaVarian(Request $request): void
    {
        if (is_string($request->input('nama_varian'))) {
            $request->merge(['nama_varian' => preg_replace('/\s+/', ' ', trim($request->input('nama_varian')))]);
        }
    }
}
