<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuktiRetur;
use App\Models\Retur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RiwayatReturController extends Controller
{
    public function index(Request $request)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->status_aktif && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan aktif.');

        $filter = $request->validate([
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $data = Retur::query()
            ->whereHas('pesanan', fn($q) => $q->where('id_pengguna', $pengguna->id_pengguna))
            ->with([
                'pesanan:id_pesanan,nomor_pesanan,status_pesanan',
                'detailRetur',
                'buktiRetur',
            ])
            ->latest('diajukan_pada')
            ->paginate((int) ($filter['per_page'] ?? 10));

        return response()->json(['message' => 'Riwayat retur berhasil diambil.', 'data' => $data]);
    }

    public function show(Request $request, int $id_retur)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->status_aktif && $pengguna->peran === 'pelanggan', 403, 'Endpoint ini khusus pelanggan aktif.');

        $retur = Retur::query()
            ->where('id_retur', $id_retur)
            ->whereHas('pesanan', fn($q) => $q->where('id_pengguna', $pengguna->id_pengguna))
            ->with([
                'pesanan:id_pesanan,nomor_pesanan,status_pesanan',
                'detailRetur.detailPesanan',
                'buktiRetur',
                'refund.percobaanRefund',
            ])
            ->firstOrFail();

        // Jangan mengirim respons gateway atau path penyimpanan privat ke aplikasi.
        // Pelanggan menerima metadata refund dan URL endpoint yang memeriksa kepemilikan.
        $riwayatRefund = $retur->refund->map(function ($refund) {
            return [
                'id_refund' => $refund->id_refund,
                'jenis_refund' => $refund->jenis_refund,
                'nominal_refund' => (int) $refund->nominal_refund,
                'status_refund' => $refund->status_refund,
                'alasan' => $refund->alasan,
                'disetujui_pada' => $refund->disetujui_pada,
                'diproses_pada' => $refund->diproses_pada,
                'selesai_pada' => $refund->selesai_pada,
                'riwayat_percobaan' => $refund->percobaanRefund
                    ->sortBy('dimulai_pada')
                    ->values()
                    ->map(function ($percobaan) {
                        $respons = is_array($percobaan->respons_gateway)
                            ? $percobaan->respons_gateway
                            : [];
                        $path = $respons['bukti_path'] ?? null;
                        $tersedia = is_string($path)
                            && str_starts_with($path, 'refund-manual/')
                            && \Illuminate\Support\Facades\Storage::disk('local')->exists($path);

                        return [
                            'id_percobaan_refund' => $percobaan->id_percobaan_refund,
                            'nominal_refund' => (int) $percobaan->nominal_refund,
                            'status_percobaan' => $percobaan->status_percobaan,
                            'referensi_gateway' => $percobaan->referensi_gateway,
                            'metode_refund' => $respons['metode_refund'] ?? null,
                            'catatan' => $respons['catatan'] ?? $percobaan->pesan_error,
                            'bukti_tersedia' => $tersedia,
                            'url_bukti_preview' => $tersedia
                                ? route('pelanggan.refund.bukti.preview', ['id_percobaan_refund' => $percobaan->id_percobaan_refund])
                                : null,
                            'url_bukti_download' => $tersedia
                                ? route('pelanggan.refund.bukti', ['id_percobaan_refund' => $percobaan->id_percobaan_refund])
                                : null,
                            'dimulai_pada' => $percobaan->dimulai_pada,
                            'selesai_pada' => $percobaan->selesai_pada,
                        ];
                    })->all(),
            ];
        })->values()->all();

        $dataRetur = $retur->toArray();
        $dataRetur['riwayat_refund'] = $riwayatRefund;

        return response()->json(['message' => 'Detail retur berhasil diambil.', 'data' => $dataRetur]);
    }
    /**
     * Bukti retur disimpan privat dan hanya dapat diunduh pemilik pesanan
     * atau Mitra aktif yang menangani operasional.
     */
    public function previewBukti(Request $request, int $id_bukti_retur)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->status_aktif, 403, 'Akun tidak aktif.');

        $bukti = BuktiRetur::query()
            ->with('retur.pesanan')
            ->findOrFail($id_bukti_retur);

        abort_unless($bukti->retur && $bukti->retur->pesanan, 404);

        if ($pengguna->peran === 'pelanggan') {
            abort_unless(
                (int) $bukti->retur->pesanan->id_pengguna === (int) $pengguna->id_pengguna,
                404
            );
        } elseif ($pengguna->peran !== 'mitra') {
            abort(403, 'Tidak memiliki akses ke bukti retur.');
        }

        $path = $bukti->file_bukti;
        abort_unless(is_string($path) && str_starts_with($path, 'retur/'), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'Bukti retur privat tidak ditemukan.');

        $absolutePath = $disk->path($path);
        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';
        abort_unless(
            str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/'),
            415,
            'Format bukti tidak dapat ditampilkan.'
        );

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function downloadBukti(Request $request, int $id_bukti_retur)
    {
        $pengguna = $request->user();
        abort_unless($pengguna && $pengguna->status_aktif, 403, 'Akun tidak aktif.');

        $bukti = BuktiRetur::query()
            ->with('retur.pesanan')
            ->findOrFail($id_bukti_retur);

        abort_unless($bukti->retur && $bukti->retur->pesanan, 404);

        if ($pengguna->peran === 'pelanggan') {
            abort_unless(
                (int) $bukti->retur->pesanan->id_pengguna === (int) $pengguna->id_pengguna,
                404
            );
        } elseif ($pengguna->peran !== 'mitra') {
            abort(403, 'Tidak memiliki akses ke bukti retur.');
        }

        $path = $bukti->file_bukti;
        abort_unless(is_string($path) && str_starts_with($path, 'retur/'), 404);

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($path),
            404,
            'Bukti retur privat tidak ditemukan. Pastikan file telah dipindahkan ke penyimpanan privat.'
        );

        return response()->download(
            $disk->path($path),
            basename($path)
        );
    }
}
