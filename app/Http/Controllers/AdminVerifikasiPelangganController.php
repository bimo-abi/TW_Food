<?php

namespace App\Http\Controllers;

use App\Models\DokumenMitra;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminVerifikasiPelangganController extends Controller
{
    public function index(): View
    {
        $pelanggan = Pengguna::with(['profilBisnis', 'dokumenMitra'])
            ->whereIn('jenis_pelanggan', ['toko', 'horeca'])
            ->where('status_verifikasi', 'menunggu_verifikasi')
            ->orderBy('id_pengguna')
            ->get();

        return view('admin.verifikasi-pelanggan.index', compact('pelanggan'));
    }

    public function show(int $id): View
    {
        $pelanggan = Pengguna::with(['profilBisnis', 'dokumenMitra'])
            ->whereIn('jenis_pelanggan', ['toko', 'horeca'])
            ->findOrFail($id);

        return view('admin.verifikasi-pelanggan.show', compact('pelanggan'));
    }

    /** Dokumen disajikan privat hanya kepada admin yang sudah melewati middleware admin. */
    public function downloadDokumen(int $idPengguna, int $idDokumen): StreamedResponse
    {
        $dokumen = DokumenMitra::where('id_pengguna', $idPengguna)
            ->where('id_dokumen_mitra', $idDokumen)
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($dokumen->file_dokumen), 404);

        return Storage::disk('local')->response(
            $dokumen->file_dokumen,
            basename($dokumen->file_dokumen),
            ['Content-Disposition' => 'inline']
        );
    }

    public function setujui(int $id): RedirectResponse
    {
        $hasil = DB::transaction(function () use ($id) {
            $pelanggan = Pengguna::whereIn('jenis_pelanggan', ['toko', 'horeca'])
                ->where('id_pengguna', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pelanggan->status_verifikasi !== 'menunggu_verifikasi') {
                return 'processed';
            }

            $dokumen = $pelanggan->dokumenMitra()->lockForUpdate()->get();
            $wajib = $dokumen->whereIn('jenis_dokumen', ['ktp', 'dokumen_pendukung']);

            if (!$wajib->contains('jenis_dokumen', 'ktp') ||
                !$wajib->contains('jenis_dokumen', 'dokumen_pendukung') ||
                $dokumen->isEmpty()) {
                return 'incomplete';
            }

            $profil = $pelanggan->profilBisnis;
            if (
                ($profil?->nib && !$dokumen->contains('jenis_dokumen', 'nib')) ||
                ($profil?->npwp && !$dokumen->contains('jenis_dokumen', 'npwp')) ||
                !$wajib->every(fn ($item) => Storage::disk('local')->exists($item->file_dokumen))
            ) {
                return 'incomplete';
            }

            $pelanggan->dokumenMitra()->update([
                'status_verifikasi' => 'disetujui',
                'catatan' => null,
            ]);
            $pelanggan->status_verifikasi = 'disetujui';
            $pelanggan->save();

            return 'ok';
        });

        if ($hasil === 'incomplete') {
            return back()->with('error', 'KTP dan minimal satu dokumen pendukung wajib tersedia sebelum persetujuan.');
        }
        if ($hasil !== 'ok') {
            return back()->with('error', 'Pelanggan ini sudah diproses atau tidak lagi menunggu verifikasi.');
        }

        return redirect()->route('admin.verifikasi-pelanggan.index')
            ->with('success', 'Pelanggan berhasil disetujui.');
    }

    public function tolak(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $hasil = DB::transaction(function () use ($id, $validated) {
            $pelanggan = Pengguna::whereIn('jenis_pelanggan', ['toko', 'horeca'])
                ->where('id_pengguna', $id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($pelanggan->status_verifikasi !== 'menunggu_verifikasi') {
                return false;
            }

            $pelanggan->dokumenMitra()->update([
                'status_verifikasi' => 'ditolak',
                'catatan' => $validated['alasan_penolakan'],
            ]);
            $pelanggan->status_verifikasi = 'ditolak';
            $pelanggan->save();

            return true;
        });

        if (!$hasil) {
            return back()->with('error', 'Pelanggan ini sudah diproses atau tidak lagi menunggu verifikasi.');
        }

        return redirect()->route('admin.verifikasi-pelanggan.index')
            ->with('success', 'Pelanggan berhasil ditolak.');
    }
}
