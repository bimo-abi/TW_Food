<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PercobaanRefund;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RefundRiwayatController extends Controller
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

    private function formatPercobaan(PercobaanRefund $percobaan, string $downloadRoute): array
    {
        $respons = is_array($percobaan->respons_gateway)
            ? $percobaan->respons_gateway
            : [];

        $pathBukti = $respons['bukti_path'] ?? null;
        $tersedia = is_string($pathBukti)
            && str_starts_with($pathBukti, 'refund-manual/')
            && Storage::disk('local')->exists($pathBukti);

        return [
            'id_percobaan_refund' => $percobaan->id_percobaan_refund,
            'penyedia_pembayaran' => $percobaan->penyedia_pembayaran,
            'nominal_refund' => (int) $percobaan->nominal_refund,
            'status_percobaan' => $percobaan->status_percobaan,
            'referensi_gateway' => $percobaan->referensi_gateway,
            'metode_refund' => $respons['metode_refund'] ?? null,
            'catatan' => $respons['catatan'] ?? $percobaan->pesan_error,
            'dicatat_oleh' => $respons['dicatat_oleh'] ?? null,
            'bukti_tersedia' => $tersedia,
            'url_bukti' => $tersedia
                ? route($downloadRoute, ['id_percobaan_refund' => $percobaan->id_percobaan_refund])
                : null,
            'dimulai_pada' => $percobaan->dimulai_pada,
            'selesai_pada' => $percobaan->selesai_pada,
        ];
    }

    private function detailRefund(Refund $refund, string $downloadRoute): array
    {
        $refund->loadMissing([
            'pesanan.pengguna',
            'pembayaran',
            'retur',
            'penggunaPenyetuju',
            'percobaanRefund',
        ]);

        return [
            'id_refund' => $refund->id_refund,
            'id_retur' => $refund->id_retur,
            'nomor_retur' => $refund->retur?->nomor_retur,
            'id_pesanan' => $refund->id_pesanan,
            'nomor_pesanan' => $refund->pesanan?->nomor_pesanan,
            'nama_pelanggan' => $refund->pesanan?->pengguna?->nama,
            'jenis_refund' => $refund->jenis_refund,
            'nominal_refund' => (int) $refund->nominal_refund,
            'status_refund' => $refund->status_refund,
            'alasan' => $refund->alasan,
            'pembayaran' => $refund->pembayaran ? [
                'id_pembayaran' => $refund->pembayaran->id_pembayaran,
                'id_transaksi' => $refund->pembayaran->id_transaksi,
                'metode_pembayaran' => $refund->pembayaran->metode_pembayaran,
                'jumlah_pembayaran' => (int) $refund->pembayaran->jumlah_pembayaran,
                'status_pembayaran' => $refund->pembayaran->status_pembayaran,
            ] : null,
            'penyetuju' => $refund->penggunaPenyetuju ? [
                'id_pengguna' => $refund->penggunaPenyetuju->id_pengguna,
                'nama' => $refund->penggunaPenyetuju->nama,
            ] : null,
            'disetujui_pada' => $refund->disetujui_pada,
            'diproses_pada' => $refund->diproses_pada,
            'selesai_pada' => $refund->selesai_pada,
            'riwayat_percobaan' => $refund->percobaanRefund
                ->sortBy('dimulai_pada')
                ->values()
                ->map(fn (PercobaanRefund $percobaan) => $this->formatPercobaan($percobaan, $downloadRoute))
                ->all(),
        ];
    }

    /**
     * Daftar riwayat refund untuk Flutter Mitra.
     */
    public function indexMitra(Request $request)
    {
        $this->pastikanMitra($request);

        $data = $request->validate([
            'status_refund' => ['nullable', 'in:belum_diajukan,menunggu_diproses,diproses,berhasil,gagal,perlu_diproses_ulang'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Refund::query()
            ->where('jenis_refund', 'retur')
            ->with(['retur', 'pesanan.pengguna', 'pembayaran', 'percobaanRefund'])
            ->latest('created_at');

        if (!empty($data['status_refund'])) {
            $query->where('status_refund', $data['status_refund']);
        }

        $paginator = $query->paginate($data['per_page'] ?? 20);

        $paginator->getCollection()->transform(
            fn (Refund $refund) => $this->detailRefund($refund, 'mitra.refund.bukti')
        );

        return response()->json([
            'message' => 'Riwayat refund berhasil diambil.',
            'data' => $paginator,
        ]);
    }

    /**
     * Detail refund dan seluruh percobaan untuk Flutter Mitra.
     */
    public function showMitra(Request $request, int $id_refund)
    {
        $this->pastikanMitra($request);

        $refund = Refund::query()
            ->where('jenis_refund', 'retur')
            ->findOrFail($id_refund);

        return response()->json([
            'message' => 'Detail refund berhasil diambil.',
            'data' => $this->detailRefund($refund, 'mitra.refund.bukti'),
        ]);
    }

    /**
     * Daftar refund untuk Admin Website. Middleware admin melindungi route.
     */
    public function indexAdmin(Request $request)
    {
        $data = $request->validate([
            'status_refund' => ['nullable', 'in:belum_diajukan,menunggu_diproses,diproses,berhasil,gagal,perlu_diproses_ulang'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Refund::query()
            ->where('jenis_refund', 'retur')
            ->with(['retur', 'pesanan.pengguna', 'pembayaran', 'percobaanRefund'])
            ->latest('created_at');

        if (!empty($data['status_refund'])) {
            $query->where('status_refund', $data['status_refund']);
        }

        $paginator = $query->paginate($data['per_page'] ?? 30);
        $paginator->getCollection()->transform(
            fn (Refund $refund) => $this->detailRefund($refund, 'admin.refund.bukti')
        );

        return response()->json([
            'message' => 'Daftar riwayat refund Admin berhasil diambil.',
            'data' => $paginator,
        ]);
    }

    /**
     * Detail refund untuk Admin Website.
     */
    public function showAdmin(int $id_refund)
    {
        $refund = Refund::query()
            ->where('jenis_refund', 'retur')
            ->findOrFail($id_refund);

        return response()->json([
            'message' => 'Detail refund Admin berhasil diambil.',
            'data' => $this->detailRefund($refund, 'admin.refund.bukti'),
        ]);
    }

    /**
     * Bukti hanya dapat diunduh setelah memastikan percobaan terkait refund retur.
     */
    public function downloadBuktiMitra(Request $request, int $id_percobaan_refund)
    {
        $this->pastikanMitra($request);

        return $this->downloadBukti($id_percobaan_refund);
    }

    /**
     * Middleware admin melindungi route unduh bukti.
     */
    public function downloadBuktiAdmin(int $id_percobaan_refund)
    {
        return $this->downloadBukti($id_percobaan_refund);
    }

    private function downloadBukti(int $id_percobaan_refund)
    {
        $percobaan = PercobaanRefund::query()
            ->whereHas('refund', function ($query) {
                $query->where('jenis_refund', 'retur');
            })
            ->findOrFail($id_percobaan_refund);

        $respons = is_array($percobaan->respons_gateway)
            ? $percobaan->respons_gateway
            : [];
        $path = $respons['bukti_path'] ?? null;

        abort_unless(
            is_string($path)
                && str_starts_with($path, 'refund-manual/')
                && Storage::disk('local')->exists($path),
            404,
            'Bukti refund tidak ditemukan.'
        );

        return Storage::disk('local')->download($path);
    }
}
