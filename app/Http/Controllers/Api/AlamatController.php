<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AlamatController extends Controller
{
    /**
     * Menampilkan daftar alamat milik
     * pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $alamat = Alamat::query()
            ->where(
                'id_pengguna',
                $request->user()->id_pengguna
            )
            ->orderByDesc('alamat_utama')
            ->orderBy('id_alamat')
            ->get();

        return response()->json([
            'message' => 'Daftar alamat berhasil diambil.',
            'data' => $alamat,
        ], 200);
    }

    /**
     * Menambahkan alamat baru untuk
     * pengguna yang sedang login.
     */
    public function store(Request $request)
    {
        // Pastikan hanya pelanggan yang dapat
        // mengelola alamat pelanggan.
        if ($request->user()->peran !== 'pelanggan') {
            return response()->json([
                'message' => 'Hanya pelanggan yang dapat menambahkan alamat.',
            ], 403);
        }

        // Validasi request.
        $validated = $request->validate([
            'label' => [
                'required',
                'string',
            ],
            'nama_penerima' => [
                'required',
                'string',
            ],
            'nomor_telepon' => [
                'required',
                'string',
            ],
            'alamat_lengkap' => [
                'required',
                'string',
            ],
            'kota' => [
                'required',
                'string',
            ],
            'provinsi' => [
                'required',
                'string',
            ],
            'kode_pos' => [
                'required',
                'string',
                'max:10',
            ],
            'kode_provinsi' => ['nullable', 'string', 'regex:/^\d{2}$/'],
            'kode_kabupaten_kota' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}$/'],
            'kode_kecamatan' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
            'kode_desa_kelurahan' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'alamat_utama' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $this->validateAdministrativeRegion($validated);

        // Nama wilayah tingkat kabupaten/kota menjadi sumber untuk kolom kota lama.
        $validated['kota'] = $validated['kabupaten_kota'] ?? $validated['kota'];

        $idPengguna = $request->user()->id_pengguna;

        try {
            $alamat = DB::transaction(function () use (
                $validated,
                $idPengguna
            ) {
                // ==========================================
                // 1. CEK MAKSIMAL 3 ALAMAT
                // ==========================================

                $jumlahAlamat = Alamat::query()
                    ->where(
                        'id_pengguna',
                        $idPengguna
                    )
                    ->count();

                if ($jumlahAlamat >= 3) {
                    throw ValidationException::withMessages([
                        'alamat' => [
                            'Maksimal 3 alamat untuk satu pelanggan.',
                        ],
                    ]);
                }

                // ==========================================
                // 2. CEK ALAMAT DUPLIKAT
                // ==========================================

                $alamatDuplikat = Alamat::query()
                    ->where(
                        'id_pengguna',
                        $idPengguna
                    )
                    ->where(
                        'alamat_lengkap',
                        $validated['alamat_lengkap']
                    )
                    ->where(
                        'kota',
                        $validated['kota']
                    )
                    ->where(
                        'provinsi',
                        $validated['provinsi']
                    )
                    ->where(
                        'kode_pos',
                        $validated['kode_pos']
                    )
                    ->exists();

                if ($alamatDuplikat) {
                    throw ValidationException::withMessages([
                        'alamat_lengkap' => [
                            'Alamat tersebut sudah terdaftar.',
                        ],
                    ]);
                }

                // ==========================================
                // 3. TENTUKAN ALAMAT UTAMA
                // ==========================================

                $alamatUtama =
                    $validated['alamat_utama'] ?? false;

                // Jika ini adalah alamat pertama,
                // otomatis jadikan alamat utama.
                if ($jumlahAlamat === 0) {
                    $alamatUtama = true;
                }

                // Jika alamat baru dipilih sebagai utama,
                // nonaktifkan alamat utama sebelumnya.
                if ($alamatUtama) {
                    Alamat::query()
                        ->where(
                            'id_pengguna',
                            $idPengguna
                        )
                        ->update([
                            'alamat_utama' => false,
                        ]);
                }

                // ==========================================
                // 4. SIMPAN ALAMAT
                // ==========================================

                return Alamat::create([
                    'id_pengguna' => $idPengguna,
                    'label' => $validated['label'],
                    'nama_penerima' =>
                    $validated['nama_penerima'],
                    'nomor_telepon' =>
                    $validated['nomor_telepon'],
                    'alamat_lengkap' =>
                    $validated['alamat_lengkap'],
                    'kota' => $validated['kota'],
                    'provinsi' =>
                    $validated['provinsi'],
                    'kode_pos' =>
                    $validated['kode_pos'],
                    'kode_provinsi' => $validated['kode_provinsi'] ?? null,
                    'kode_kabupaten_kota' => $validated['kode_kabupaten_kota'] ?? null,
                    'kode_kecamatan' => $validated['kode_kecamatan'] ?? null,
                    'kode_desa_kelurahan' => $validated['kode_desa_kelurahan'] ?? null,
                    'kabupaten_kota' => $validated['kabupaten_kota'] ?? $validated['kota'],
                    'kecamatan' => $validated['kecamatan'] ?? null,
                    'desa_kelurahan' => $validated['desa_kelurahan'] ?? null,
                    'latitude' =>
                    $validated['latitude'] ?? null,
                    'longitude' =>
                    $validated['longitude'] ?? null,
                    'alamat_utama' => $alamatUtama,
                ]);
            });

            return response()->json([
                'message' => 'Alamat berhasil ditambahkan.',
                'data' => $alamat,
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Mengubah alamat milik pengguna yang sedang login.
     */
    public function update(
        Request $request,
        int $idAlamat
    ) {
        // Pastikan hanya pelanggan yang dapat
        // mengelola alamat pelanggan.
        if ($request->user()->peran !== 'pelanggan') {
            return response()->json([
                'message' => 'Hanya pelanggan yang dapat mengubah alamat.',
            ], 403);
        }

        // Cari alamat yang benar-benar dimiliki
        // oleh pengguna yang sedang login.
        $alamat = Alamat::query()
            ->where(
                'id_alamat',
                $idAlamat
            )
            ->where(
                'id_pengguna',
                $request->user()->id_pengguna
            )
            ->first();

        if ($alamat === null) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        // Validasi request.
        $validated = $request->validate([
            'label' => [
                'required',
                'string',
            ],
            'nama_penerima' => [
                'required',
                'string',
            ],
            'nomor_telepon' => [
                'required',
                'string',
            ],
            'alamat_lengkap' => [
                'required',
                'string',
            ],
            'kota' => [
                'required',
                'string',
            ],
            'provinsi' => [
                'required',
                'string',
            ],
            'kode_pos' => [
                'required',
                'string',
                'max:10',
            ],
            'kode_provinsi' => ['nullable', 'string', 'regex:/^\d{2}$/'],
            'kode_kabupaten_kota' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}$/'],
            'kode_kecamatan' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}$/'],
            'kode_desa_kelurahan' => ['nullable', 'string', 'regex:/^\d{2}\.\d{2}\.\d{2}\.\d{4}$/'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'desa_kelurahan' => ['nullable', 'string', 'max:100'],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'alamat_utama' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $this->validateAdministrativeRegion($validated);

        // Nama wilayah tingkat kabupaten/kota menjadi sumber untuk kolom kota lama.
        $validated['kota'] = $validated['kabupaten_kota'] ?? $validated['kota'];

        $idPengguna = $request->user()->id_pengguna;

        try {
            $alamat = DB::transaction(function () use (
                $validated,
                $idPengguna,
                $alamat
            ) {
                // ==========================================
                // 1. CEK DUPLIKAT
                // ==========================================

                $alamatDuplikat = Alamat::query()
                    ->where(
                        'id_pengguna',
                        $idPengguna
                    )
                    ->where(
                        'id_alamat',
                        '!=',
                        $alamat->id_alamat
                    )
                    ->where(
                        'alamat_lengkap',
                        $validated['alamat_lengkap']
                    )
                    ->where(
                        'kota',
                        $validated['kota']
                    )
                    ->where(
                        'provinsi',
                        $validated['provinsi']
                    )
                    ->where(
                        'kode_pos',
                        $validated['kode_pos']
                    )
                    ->exists();

                if ($alamatDuplikat) {
                    throw ValidationException::withMessages([
                        'alamat_lengkap' => [
                            'Alamat tersebut sudah terdaftar.',
                        ],
                    ]);
                }

                // ==========================================
                // 2. TENTUKAN ALAMAT UTAMA
                // ==========================================

                $alamatUtama =
                    $validated['alamat_utama'] ?? false;

                if ($alamatUtama) {
                    Alamat::query()
                        ->where(
                            'id_pengguna',
                            $idPengguna
                        )
                        ->where(
                            'id_alamat',
                            '!=',
                            $alamat->id_alamat
                        )
                        ->update([
                            'alamat_utama' => false,
                        ]);
                }

                // ==========================================
                // 3. UPDATE ALAMAT
                // ==========================================

                $alamat->update([
                    'label' =>
                    $validated['label'],
                    'nama_penerima' =>
                    $validated['nama_penerima'],
                    'nomor_telepon' =>
                    $validated['nomor_telepon'],
                    'alamat_lengkap' =>
                    $validated['alamat_lengkap'],
                    'kota' =>
                    $validated['kota'],
                    'provinsi' =>
                    $validated['provinsi'],
                    'kode_pos' =>
                    $validated['kode_pos'],
                    // Pertahankan metadata wilayah/koordinat jika request berasal
                    // dari aplikasi lama yang belum mengirim field baru.
                    'kode_provinsi' => $validated['kode_provinsi'] ?? $alamat->kode_provinsi,
                    'kode_kabupaten_kota' => $validated['kode_kabupaten_kota'] ?? $alamat->kode_kabupaten_kota,
                    'kode_kecamatan' => $validated['kode_kecamatan'] ?? $alamat->kode_kecamatan,
                    'kode_desa_kelurahan' => $validated['kode_desa_kelurahan'] ?? $alamat->kode_desa_kelurahan,
                    'kabupaten_kota' => $validated['kabupaten_kota'] ?? $alamat->kabupaten_kota ?? $validated['kota'],
                    'kecamatan' => $validated['kecamatan'] ?? $alamat->kecamatan,
                    'desa_kelurahan' => $validated['desa_kelurahan'] ?? $alamat->desa_kelurahan,
                    'latitude' => $validated['latitude'] ?? $alamat->latitude,
                    'longitude' => $validated['longitude'] ?? $alamat->longitude,
                    'alamat_utama' =>
                    $alamatUtama,
                ]);

                return $alamat->fresh();
            });

            return response()->json([
                'message' => 'Alamat berhasil diubah.',
                'data' => $alamat,
            ], 200);
        } catch (ValidationException $e) {
            throw $e;
        }
    }
    /**
     * Menghapus alamat milik pengguna yang sedang login.
     */
    public function destroy(
        Request $request,
        int $idAlamat
    ) {
        // Pastikan hanya pelanggan yang dapat menghapus alamat.
        if (
            !$request->user() ||
            $request->user()->peran !== 'pelanggan'
        ) {
            return response()->json([
                'message' => 'Hanya pelanggan yang dapat menghapus alamat.',
            ], 403);
        }

        // Cari alamat berdasarkan ID dan pemiliknya.
        $alamat = Alamat::query()
            ->where('id_alamat', $idAlamat)
            ->where(
                'id_pengguna',
                $request->user()->id_pengguna
            )
            ->first();

        // Jika alamat tidak ditemukan atau bukan milik pengguna.
        if (!$alamat) {
            return response()->json([
                'message' => 'Alamat tidak ditemukan.',
            ], 404);
        }

        $alamatUtama = $alamat->alamat_utama;

        DB::transaction(function () use (
            $alamat,
            $alamatUtama,
            $request
        ) {
            // Hapus alamat.
            $alamat->delete();

            // Jika alamat utama dihapus dan masih ada
            // alamat lain, jadikan alamat pertama sebagai utama.
            if ($alamatUtama) {
                $alamatBaruUtama = Alamat::query()
                    ->where(
                        'id_pengguna',
                        $request->user()->id_pengguna
                    )
                    ->orderBy('id_alamat')
                    ->first();

                if ($alamatBaruUtama) {
                    $alamatBaruUtama->update([
                        'alamat_utama' => true,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ], 200);
    }

    /**
     * Kode wilayah harus dikirim lengkap sebagai satu hierarki.
     * Pemeriksaan ini memvalidasi bentuk kode; verifikasi kecocokan
     * nama dan hubungan parent-child dengan sumber wilayah dilakukan
     * pada tahap integrasi layanan wilayah berikutnya.
     */
    private function validateAdministrativeRegion(array $validated): void
    {
        $keys = [
            'kode_provinsi',
            'kode_kabupaten_kota',
            'kode_kecamatan',
            'kode_desa_kelurahan',
        ];

        $provided = collect($keys)
            ->filter(fn (string $key) => !empty($validated[$key] ?? null))
            ->count();

        if ($provided !== 0 && $provided !== count($keys)) {
            throw ValidationException::withMessages([
                'kode_wilayah' => [
                    'Kode provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan harus dikirim lengkap.',
                ],
            ]);
        }

        if ($provided === count($keys)) {
            $province = $validated['kode_provinsi'];
            $regency = $validated['kode_kabupaten_kota'];
            $district = $validated['kode_kecamatan'];
            $village = $validated['kode_desa_kelurahan'];

            if (
                !str_starts_with($regency, $province . '.') ||
                !str_starts_with($district, $regency . '.') ||
                !str_starts_with($village, $district . '.')
            ) {
                throw ValidationException::withMessages([
                    'kode_wilayah' => [
                        'Hierarki kode wilayah tidak saling berhubungan.',
                    ],
                ]);
            }
        }
    }
}
