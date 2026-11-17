<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\ProduksiPreOrder;
use App\Models\RiwayatAktivitasTransaksi;
use Illuminate\Validation\ValidationException;

class ProduksiPreOrderService
{
    /** Tandai seluruh kebutuhan produksi PO selesai saat pesanan dinyatakan siap. */
    public function selesaikanUntukPesanan(Pesanan $pesanan, $aktor, string $sumber): void
    {
        if ($pesanan->jenis_pemenuhan !== 'pre_order') {
            return;
        }

        $detailIds = $pesanan->detailPesanan()->lockForUpdate()->pluck('id_detail_pesanan');
        if ($detailIds->isEmpty()) {
            throw ValidationException::withMessages(['status_pesanan' => ['Pesanan pre-order tidak memiliki detail untuk diselesaikan produksinya.']]);
        }

        $produksi = ProduksiPreOrder::whereIn('id_detail_pesanan', $detailIds)->lockForUpdate()->get();
        if ($produksi->count() !== $detailIds->count()) {
            throw ValidationException::withMessages(['status_pesanan' => ['Data produksi belum tersedia untuk seluruh detail pesanan pre-order.']]);
        }

        if ($produksi->contains(fn ($item) => !in_array($item->status_produksi, ['diproses', 'selesai'], true))) {
            throw ValidationException::withMessages(['status_pesanan' => ['Produksi pre-order belum dimulai atau masih menunggu pembayaran terverifikasi.']]);
        }

        $jumlahBelumSelesai = $produksi->where('status_produksi', 'diproses')->count();
        foreach ($produksi as $item) {
            if ($item->status_produksi !== 'selesai') {
                $item->status_produksi = 'selesai';
                $item->selesai_produksi_pada = now();
                $item->save();
            }
        }

        if ($jumlahBelumSelesai > 0) {
            RiwayatAktivitasTransaksi::create([
                'id_pengguna' => $aktor?->id_pengguna,
                'jenis_aktor' => $aktor?->peran === 'mitra' ? 'mitra' : ($aktor ? 'admin' : 'sistem'),
                'id_pesanan' => $pesanan->id_pesanan,
                'id_pembayaran' => null,
                'jenis_aktivitas' => 'produksi_pre_order_selesai',
                'status_sebelum' => 'diproses',
                'status_sesudah' => 'selesai',
                'deskripsi' => 'Seluruh produksi pre-order diselesaikan melalui ' . $sumber . '.',
                'created_at' => now(),
            ]);
        }
    }
}
