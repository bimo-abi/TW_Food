<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:pengguna,email',
            'nomor_telepon' => 'required|string|max:20',
            'kata_sandi' => 'required|string|min:8|confirmed',

            'jenis_pelanggan' => 'required|in:umum,toko,horeca',

            'profil_bisnis' => 'required_if:jenis_pelanggan,toko,horeca|array',

            'profil_bisnis.nama_bisnis' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:150',

            'profil_bisnis.nama_pic' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:100',

            'profil_bisnis.nomor_telepon_bisnis' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:20',

            'profil_bisnis.alamat_bisnis' =>
            'required_if:jenis_pelanggan,toko,horeca|string',

            'profil_bisnis.kota' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:100',

            'profil_bisnis.provinsi' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:100',

            'profil_bisnis.kode_pos' =>
            'required_if:jenis_pelanggan,toko,horeca|string|max:10',

            'profil_bisnis.nib' =>
            'nullable|string|max:100',

            'profil_bisnis.npwp' =>
            'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data registrasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pengguna = DB::transaction(function () use ($request) {
            $pengguna = Pengguna::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'nomor_telepon' => $request->nomor_telepon,
                'kata_sandi' => Hash::make($request->kata_sandi),
                'peran' => 'pelanggan',
                'jenis_pelanggan' => $request->jenis_pelanggan,
                'status_aktif' => true,
            ]);

            if (
                in_array(
                    $request->jenis_pelanggan,
                    ['toko', 'horeca']
                )
            ) {
                $profilBisnis = $request->profil_bisnis;

                $pengguna->profilBisnis()->create([
                    'jenis_bisnis' => $request->jenis_pelanggan,
                    'nama_bisnis' => $profilBisnis['nama_bisnis'],
                    'nama_pic' => $profilBisnis['nama_pic'],
                    'nomor_telepon_bisnis' =>
                    $profilBisnis['nomor_telepon_bisnis'],
                    'alamat_bisnis' =>
                    $profilBisnis['alamat_bisnis'],
                    'kota' =>
                    $profilBisnis['kota'],
                    'provinsi' =>
                    $profilBisnis['provinsi'],
                    'kode_pos' =>
                    $profilBisnis['kode_pos'],
                    'nib' =>
                    $profilBisnis['nib'] ?? null,
                    'npwp' =>
                    $profilBisnis['npwp'] ?? null,
                ]);
            }

            return $pengguna;
        });

        return response()->json([
            'message' => 'Registrasi pelanggan berhasil.',
            'data' => [
                'id_pengguna' => $pengguna->id_pengguna,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'nomor_telepon' => $pengguna->nomor_telepon,
                'peran' => $pengguna->peran,
                'jenis_pelanggan' => $pengguna->jenis_pelanggan,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'kata_sandi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data login tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $pengguna = Pengguna::where(
            'email',
            $request->email
        )->first();

        if (!$pengguna || !Hash::check(
            $request->kata_sandi,
            $pengguna->kata_sandi
        )) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (!$pengguna->status_aktif) {
            return response()->json([
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        $token = $pengguna->createToken(
            'twfood-mobile'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'id_pengguna' => $pengguna->id_pengguna,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'nomor_telepon' => $pengguna->nomor_telepon,
                'peran' => $pengguna->peran,
                'jenis_pelanggan' => $pengguna->jenis_pelanggan,
                'token' => $token,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ], 200);
    }

    public function me(Request $request)
    {
        $pengguna = $request->user();

        return response()->json([
            'message' => 'Data pengguna berhasil diambil.',
            'data' => [
                'id_pengguna' => $pengguna->id_pengguna,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'nomor_telepon' => $pengguna->nomor_telepon,
                'peran' => $pengguna->peran,
                'jenis_pelanggan' => $pengguna->jenis_pelanggan,
                'status_aktif' => $pengguna->status_aktif,
            ],
        ], 200);
    }
}
