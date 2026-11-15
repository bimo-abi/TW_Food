<?php

namespace App\Http\Controllers;

use App\Services\ManualRefundService;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    public function index()
    {
        return view('admin.retur.index');
    }

    /**
     * Admin Website mencatat hasil refund manual melalui service bersama
     * yang juga digunakan endpoint Flutter Mitra.
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
            ->route('admin.retur.index')
            ->with(
                'success',
                $data['hasil'] === 'berhasil'
                    ? 'Hasil refund manual berhasil dicatat.'
                    : 'Kegagalan percobaan refund berhasil dicatat.'
            );
    }
}
