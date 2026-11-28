<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Services\MidtransRefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /** Admin/Mitra aktif memulai refund otomatis setelah syarat bisnis terpenuhi. */
    public function prosesOtomatis(Request $request, int $id_refund, MidtransRefundService $service)
    {
        abort_unless(
            $request->user() && $request->user()->peran === 'mitra' && $request->user()->status_aktif,
            403,
            'Akses khusus Mitra aktif.'
        );

        $refund = Refund::query()->with('retur', 'pesanan')->findOrFail($id_refund);
        if ($refund->jenis_refund === 'retur') {
            abort_unless($refund->retur && $refund->retur->status_retur === 'sedang_diperiksa', 422,
                'Refund retur hanya dapat diproses setelah pemeriksaan barang.');
        } elseif ($refund->jenis_refund === 'pembatalan_operasional') {
            abort_unless($refund->pesanan && $refund->pesanan->status_pesanan === 'dibatalkan', 422,
                'Refund pembatalan operasional hanya dapat diproses setelah pesanan dibatalkan.');
        } else {
            abort(422, 'Jenis refund tidak dikenali.');
        }

        $result = $service->proses($id_refund, (int) $request->user()->id_pengguna);
        if ($request->expectsJson()) {
            return response()->json(['message' => $result['message'], 'data' => [
                'refund' => $result['refund'],
                'attempt' => $result['attempt'],
            ]]);
        }

        return redirect()->route('admin.refund.show', ['id_refund' => $id_refund])->with('success', $result['message']);
    }
}
