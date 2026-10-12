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
                'minimal_quantity_dp' => 10,
                'persentase_dp' => 50.00,
                'dp_aktif' => true,
                'notifikasi_aktif' => false,
            ]
        );

        return view(
            'admin.pengaturan-mitra.index',
            compact('pengaturan')
        );
    }

    /**
     * Menyimpan perubahan pengaturan Mitra.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'minimal_quantity_dp' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],
            'persentase_dp' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
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
                'minimal_quantity_dp' => 10,
                'persentase_dp' => 50.00,
                'dp_aktif' => true,
                'notifikasi_aktif' => false,
            ]
        );

        $pengaturan->minimal_quantity_dp =
            $validated['minimal_quantity_dp'];

        $pengaturan->persentase_dp =
            $validated['persentase_dp'];

        // Checkbox yang tidak dicentang dianggap nonaktif.
        $pengaturan->dp_aktif =
            $request->boolean('dp_aktif');

        $pengaturan->notifikasi_aktif =
            $request->boolean('notifikasi_aktif');

        $pengaturan->save();

        return redirect()
            ->route('admin.pengaturan-mitra.index')
            ->with(
                'success',
                'Pengaturan Mitra berhasil diperbarui.'
            );
    }
}
