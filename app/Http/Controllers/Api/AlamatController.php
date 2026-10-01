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
            ],
            'latitude' => [
                'nullable',
                'numeric',
            ],
            'longitude' => [
                'nullable',
                'numeric',
            ],
            'alamat_utama' => [
                'sometimes',
                'boolean',
            ],
        ]);

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
            ],
            'latitude' => [
                'nullable',
                'numeric',
            ],
            'longitude' => [
                'nullable',
                'numeric',
            ],
            'alamat_utama' => [
                'sometimes',
                'boolean',
            ],
        ]);

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
                    'latitude' =>
                    $validated['latitude'] ?? null,
                    'longitude' =>
                    $validated['longitude'] ?? null,
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
}
