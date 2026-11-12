<?php

namespace App\Http\Controllers;

use App\Models\PengaturanMitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPengaturanMitraController extends Controller
{
    /**
     * Menampilkan pengaturan milik Mitra yang sedang login.
     */
    public function index()
    {
        $pengguna = Auth::user();

        $pengaturan = PengaturanMitra::firstOrCreate(
            [
                'id_pengguna' => $pengguna->id_pengguna,
            ],
            [
                'minimal_nominal_dp' => null,
                'persentase_dp' => 50.00,
                'dp_aktif' => true,
                'notifikasi_aktif' => false,
            ]
        );

        // Persentase DP ditetapkan 50% sesuai PRD dan tidak dapat diubah pengguna.
        if ((float) $pengaturan->persentase_dp !== 50.00) {
            $pengaturan->persentase_dp = 50.00;
            $pengaturan->save();
        }

        return view(
            'admin.pengaturan-mitra.index',
            compact('pengaturan')
        );
    }

    /**
     * Menyimpan pengaturan Mitra.
     * Nominal minimum dapat diatur, tetapi persentase DP selalu 50%.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'minimal_nominal_dp' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],
            'dp_aktif' => [
                'sometimes',
                'boolean',
            ],
            'notifikasi_aktif' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $pengguna = Auth::user();

        $pengaturan = PengaturanMitra::firstOrCreate(
            [
                'id_pengguna' => $pengguna->id_pengguna,
            ],
            [
                'minimal_nominal_dp' => null,
                'persentase_dp' => 50.00,
                'dp_aktif' => true,
                'notifikasi_aktif' => false,
            ]
        );

        $pengaturan->minimal_nominal_dp =
            $validated['minimal_nominal_dp'];

        // Nilai DP tidak diambil dari request agar tidak dapat diubah dari client.
        $pengaturan->persentase_dp = 50.00;

        // Checkbox yang tidak dicentang dianggap nonaktif.
        $pengaturan->dp_aktif = $request->boolean('dp_aktif');
        $pengaturan->notifikasi_aktif = $request->boolean('notifikasi_aktif');

        $pengaturan->save();

        return redirect()
            ->route('admin.pengaturan-mitra.index')
            ->with(
                'success',
                'Pengaturan Mitra berhasil diperbarui.'
            );
    }
}
