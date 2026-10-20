<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminVerifikasiPelangganController extends Controller
{
    /**
     * Menampilkan daftar pelanggan yang menunggu verifikasi.
     */
    public function index(): View
    {
        $pelanggan = Pengguna::with('profilBisnis')
            ->whereIn('jenis_pelanggan', ['toko', 'horeca'])
            ->where('status_verifikasi', 'menunggu_verifikasi')
            ->orderBy('id_pengguna')
            ->get();

        return view('admin.verifikasi-pelanggan.index', compact('pelanggan'));
    }

    /**
     * Menampilkan detail pelanggan dan profil bisnisnya.
     */
    public function show(int $id): View
    {
        $pelanggan = Pengguna::with('profilBisnis')
            ->whereIn('jenis_pelanggan', ['toko', 'horeca'])
            ->findOrFail($id);

        return view('admin.verifikasi-pelanggan.show', compact('pelanggan'));
    }

    /**
     * Menyetujui pelanggan yang masih menunggu verifikasi.
     */
    public function setujui(int $id): RedirectResponse
    {
        return $this->ubahStatusVerifikasi(
            $id,
            'disetujui',
            'Pelanggan berhasil disetujui.'
        );
    }

    /**
     * Menolak pelanggan yang masih menunggu verifikasi.
     */
    public function tolak(int $id): RedirectResponse
    {
        return $this->ubahStatusVerifikasi(
            $id,
            'ditolak',
            'Pelanggan berhasil ditolak.'
        );
    }

    /**
     * Mengubah status hanya jika status sebelumnya masih menunggu verifikasi.
     */
    private function ubahStatusVerifikasi(
        int $id,
        string $status,
        string $pesan
    ): RedirectResponse {
        $hasil = DB::transaction(function () use ($id, $status) {
            $pelanggan = Pengguna::whereIn(
                'jenis_pelanggan',
                ['toko', 'horeca']
            )
                ->where('id_pengguna', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pelanggan->status_verifikasi !== 'menunggu_verifikasi') {
                return false;
            }

            $pelanggan->status_verifikasi = $status;
            $pelanggan->save();

            return true;
        });

        if (! $hasil) {
            return back()->with(
                'error',
                'Pelanggan ini sudah diproses atau tidak lagi menunggu verifikasi.'
            );
        }

        return redirect()
            ->route('admin.verifikasi-pelanggan.index')
            ->with('success', $pesan);
    }
}
