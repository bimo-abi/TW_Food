<?php

namespace App\Http\Controllers;

use App\Models\BuktiRetur;
use App\Models\Retur;
use App\Services\ManualRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'status_retur' => [
                'nullable',
                'in:menunggu_persetujuan,disetujui,menunggu_pengembalian_barang,barang_diterima,sedang_diperiksa,selesai,ditolak,dibatalkan',
            ],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Retur::query()
            ->with([
                'pesanan.pengguna',
                'detailRetur',
                'refund',
            ])
            ->latest('diajukan_pada');

        if (!empty($filter['status_retur'])) {
            $query->where('status_retur', $filter['status_retur']);
        }

        if (!empty($filter['q'])) {
            $kataKunci = trim($filter['q']);
            $query->where(function ($builder) use ($kataKunci) {
                $builder->where('nomor_retur', 'like', '%' . $kataKunci . '%')
                    ->orWhereHas('pesanan', function ($pesanan) use ($kataKunci) {
                        $pesanan->where('nomor_pesanan', 'like', '%' . $kataKunci . '%')
                            ->orWhereHas('pengguna', function ($pengguna) use ($kataKunci) {
                                $pengguna->where('nama', 'like', '%' . $kataKunci . '%');
                            });
                    });
            });
        }

        $retur = $query->paginate((int) ($filter['per_page'] ?? 15))
            ->withQueryString();

        return view('admin.retur.index', compact('retur', 'filter'));
    }

    public function show(int $id_retur)
    {
        $retur = Retur::query()
            ->with([
                'pesanan.pengguna',
                'detailRetur.detailPesanan',
                'buktiRetur',
                'refund.pembayaran',
                'refund.percobaanRefund',
            ])
            ->findOrFail($id_retur);

        return view('admin.retur.show', compact('retur'));
    }

    public function downloadBukti(int $id_bukti_retur)
    {
        $bukti = BuktiRetur::query()
            ->with('retur.pesanan')
            ->findOrFail($id_bukti_retur);

        abort_unless($bukti->retur && $bukti->retur->pesanan, 404);

        $path = $bukti->file_bukti;
        abort_unless(is_string($path) && str_starts_with($path, 'retur/'), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404, 'Bukti retur tidak ditemukan.');

        return response()->download($disk->path($path), basename($path));
    }

    /**
     * Mencatat hasil refund manual oleh Mitra yang mengakses Admin Website.
     * Route dilindungi middleware admin yang saat ini mensyaratkan Mitra aktif.
     */
    public function catatRefundManual(Request $request, int $id_refund, ManualRefundService $manualRefundService)
    {
        abort_unless(
            $request->user()
                && $request->user()->peran === 'mitra'
                && $request->user()->status_aktif,
            403,
            'Akses khusus Mitra aktif.'
        );

        $data = $request->validate([
            'hasil' => ['required', 'in:berhasil,gagal'],
            'metode_refund' => ['required', 'in:midtrans_dashboard,transfer_bank,lainnya'],
            'referensi_transaksi' => ['required_if:hasil,berhasil', 'nullable', 'string', 'max:100'],
            'catatan' => ['required_if:hasil,gagal', 'nullable', 'string', 'min:5', 'max:2000'],
            'bukti_refund' => ['required_if:hasil,berhasil', 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $manualRefundService->catatHasil(
            $id_refund,
            $request->user(),
            $data,
            $request->file('bukti_refund')
        );

        return redirect()
            ->route('admin.refund.show', ['id_refund' => $id_refund])
            ->with(
                'success',
                $data['hasil'] === 'berhasil'
                    ? 'Hasil refund manual berhasil dicatat.'
                    : 'Kegagalan percobaan refund berhasil dicatat.'
            );
    }
}
