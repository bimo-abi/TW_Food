<?php

namespace App\Services;

use App\Models\DetailRetur;
use App\Models\Pembayaran;
use App\Models\Refund;
use App\Models\Retur;
use App\Models\Pengguna;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundReturCalculationService
{
    /**
     * Menghitung dan mencatat refund manual untuk satu retur.
     *
     * Satu retur dapat menghasilkan beberapa baris refund apabila pesanan
     * dibayar melalui lebih dari satu transaksi (misalnya DP dan pelunasan).
     * Refund dialokasikan ke transaksi pembayaran yang lebih awal terlebih dahulu.
     *
     * Metode ini dipanggil di dalam transaksi database pemeriksaan retur.
     */
    public function hitungDanCatat(Retur $retur, Pengguna $aktor): array
    {
        if ($retur->refund()->exists()) {
            throw ValidationException::withMessages([
                'refund' => ['Catatan refund untuk pengajuan ini sudah tersedia. Perhitungan ulang ditolak untuk mencegah refund ganda.'],
            ]);
        }

        $retur->loadMissing(['pesanan', 'detailRetur.detailPesanan']);

        if (!$retur->pesanan) {
            throw ValidationException::withMessages([
                'pesanan' => ['Pesanan untuk pengajuan retur tidak ditemukan.'],
            ]);
        }

        $detailRetur = $retur->detailRetur;
        if ($detailRetur->isEmpty()) {
            throw ValidationException::withMessages([
                'detail_retur' => ['Detail retur tidak ditemukan.'],
            ]);
        }

        $nominalTotal = 0;
        $ringkasanDetail = [];

        foreach ($detailRetur as $detail) {
            $jumlahDisetujui = (int) ($detail->jumlah_disetujui ?? 0);
            $detailPesanan = $detail->detailPesanan;

            if (!$detailPesanan) {
                throw ValidationException::withMessages([
                    'detail_retur' => ['Detail pesanan untuk salah satu item retur tidak ditemukan.'],
                ]);
            }

            $jumlahDibeli = (int) $detailPesanan->jumlah;
            if ($jumlahDibeli < 1 || $jumlahDisetujui < 0 || $jumlahDisetujui > $jumlahDibeli) {
                throw ValidationException::withMessages([
                    'detail_retur' => ['Jumlah yang disetujui tidak valid untuk perhitungan refund.'],
                ]);
            }

            if ($jumlahDisetujui === 0) {
                $detail->nominal_refund = 0;
                $detail->diskon_dialokasikan = 0;
                $detail->save();
                $ringkasanDetail[] = [
                    'id_detail_retur' => $detail->id_detail_retur,
                    'nominal_refund' => 0,
                    'diskon_dialokasikan' => 0,
                ];
                continue;
            }

            $subtotalKotor = (int) ($detailPesanan->subtotal
                ?? ((int) $detailPesanan->harga_saat_pesan * $jumlahDibeli));
            $diskonItem = max(0, (int) ($detailPesanan->diskon_item ?? 0));
            $subtotalBersih = $detailPesanan->subtotal_setelah_diskon !== null
                ? (int) $detailPesanan->subtotal_setelah_diskon
                : max(0, $subtotalKotor - $diskonItem);

            if ($subtotalBersih < 0 || $subtotalBersih > $subtotalKotor) {
                throw ValidationException::withMessages([
                    'detail_retur' => ['Snapshot harga setelah diskon tidak valid. Perhitungan refund dihentikan.'],
                ]);
            }

            // Jumlah dan nilai retur yang telah disetujui pada retur sebelumnya
            // untuk baris detail pesanan yang sama.
            $returSebelumnya = Retur::query()
                ->where('id_pesanan', $retur->id_pesanan)
                ->where('id_retur', '<>', $retur->id_retur)
                ->whereIn('status_retur', [
                    'disetujui',
                    'menunggu_pengembalian_barang',
                    'barang_diterima',
                    'sedang_diperiksa',
                    'selesai',
                ])
                ->whereHas('detailRetur', function ($query) use ($detail) {
                    $query->where('id_detail_pesanan', $detail->id_detail_pesanan);
                })
                ->with(['detailRetur' => function ($query) use ($detail) {
                    $query->where('id_detail_pesanan', $detail->id_detail_pesanan);
                }])
                ->lockForUpdate()
                ->get();

            $jumlahDisetujuiSebelumnya = 0;
            $nominalSebelumnya = 0;
            $diskonSebelumnya = 0;

            foreach ($returSebelumnya as $returLama) {
                foreach ($returLama->detailRetur as $detailLama) {
                    $jumlahDisetujuiSebelumnya += (int) ($detailLama->jumlah_disetujui ?? 0);
                    $nominalSebelumnya += (int) ($detailLama->nominal_refund ?? 0);
                    $diskonSebelumnya += (int) ($detailLama->diskon_dialokasikan ?? 0);
                }
            }

            $jumlahKumulatif = $jumlahDisetujuiSebelumnya + $jumlahDisetujui;
            if ($jumlahKumulatif > $jumlahDibeli) {
                throw ValidationException::withMessages([
                    'detail_retur' => ['Akumulasi jumlah retur yang disetujui melebihi jumlah pembelian.'],
                ]);
            }

            // Alokasi kumulatif menghindari selisih pembulatan ketika satu
            // baris produk dikembalikan melalui beberapa pengajuan retur.
            $targetRefundKumulatif = intdiv($subtotalBersih * $jumlahKumulatif, $jumlahDibeli);
            $targetDiskonKumulatif = intdiv($diskonItem * $jumlahKumulatif, $jumlahDibeli);

            // Kompatibilitas untuk retur lama yang belum menyimpan nominal per detail.
            if ($nominalSebelumnya === 0 && $jumlahDisetujuiSebelumnya > 0) {
                $nominalSebelumnya = intdiv(
                    $subtotalBersih * $jumlahDisetujuiSebelumnya,
                    $jumlahDibeli
                );
            }
            if ($diskonSebelumnya === 0 && $jumlahDisetujuiSebelumnya > 0) {
                $diskonSebelumnya = intdiv(
                    $diskonItem * $jumlahDisetujuiSebelumnya,
                    $jumlahDibeli
                );
            }

            $nominalDetail = max(0, $targetRefundKumulatif - $nominalSebelumnya);
            $diskonDialokasikan = max(0, $targetDiskonKumulatif - $diskonSebelumnya);

            $detail->nominal_refund = $nominalDetail;
            $detail->diskon_dialokasikan = $diskonDialokasikan;
            $detail->save();

            $nominalTotal += $nominalDetail;
            $ringkasanDetail[] = [
                'id_detail_retur' => $detail->id_detail_retur,
                'jumlah_disetujui' => $jumlahDisetujui,
                'diskon_dialokasikan' => $diskonDialokasikan,
                'nominal_refund' => $nominalDetail,
            ];
        }

        if ($nominalTotal < 1) {
            throw ValidationException::withMessages([
                'nominal_refund' => ['Nominal refund hasil perhitungan kurang dari Rp1. Periksa harga dan jumlah item yang disetujui.'],
            ]);
        }

        // Hanya transaksi pembayaran yang benar-benar berstatus paid yang
        // dapat menjadi sumber dana refund.
        // Kunci pesanan untuk menyerialkan alokasi refund dari beberapa retur
        // pada pesanan yang sama.
        \App\Models\Pesanan::query()
            ->where('id_pesanan', $retur->id_pesanan)
            ->lockForUpdate()
            ->firstOrFail();

        $pembayaran = Pembayaran::query()
            ->where('id_pesanan', $retur->id_pesanan)
            ->where('status_pembayaran', 'paid')
            ->orderBy('dibayar_pada')
            ->orderBy('id_pembayaran')
            ->lockForUpdate()
            ->get();

        if ($pembayaran->isEmpty()) {
            throw ValidationException::withMessages([
                'pembayaran' => ['Tidak ditemukan transaksi pembayaran berhasil untuk pesanan ini. Refund tidak dapat disiapkan.'],
            ]);
        }

        $sisaNominal = $nominalTotal;
        $alokasiPembayaran = [];

        foreach ($pembayaran as $pembayaranAsli) {
            $refundTerpakai = (int) Refund::query()
                ->where('id_pembayaran', $pembayaranAsli->id_pembayaran)
                ->whereIn('status_refund', [
                    'belum_diajukan',
                    'menunggu_diproses',
                    'diproses',
                    'berhasil',
                    'gagal',
                    'perlu_diproses_ulang',
                ])
                ->sum('nominal_refund');

            $tersedia = max(0, (int) $pembayaranAsli->jumlah_pembayaran - $refundTerpakai);
            if ($tersedia < 1) {
                continue;
            }

            $alokasi = min($sisaNominal, $tersedia);
            if ($alokasi > 0) {
                $alokasiPembayaran[] = [
                    'pembayaran' => $pembayaranAsli,
                    'nominal' => $alokasi,
                ];
                $sisaNominal -= $alokasi;
            }

            if ($sisaNominal === 0) {
                break;
            }
        }

        if ($sisaNominal > 0) {
            throw ValidationException::withMessages([
                'nominal_refund' => ['Nominal refund melebihi total dana pembayaran yang masih tersedia setelah memperhitungkan refund lain.'],
            ]);
        }

        $refundTercatat = [];
        foreach ($alokasiPembayaran as $alokasi) {
            /** @var Pembayaran $pembayaranAsli */
            $pembayaranAsli = $alokasi['pembayaran'];

            $refundTercatat[] = Refund::create([
                'id_pesanan' => $retur->id_pesanan,
                'id_pembayaran' => $pembayaranAsli->id_pembayaran,
                'id_retur' => $retur->id_retur,
                'jenis_refund' => 'retur',
                'nominal_refund' => $alokasi['nominal'],
                'status_refund' => 'menunggu_diproses',
                'alasan' => 'Refund manual untuk retur ' . $retur->nomor_retur,
                'id_pengguna_penyetuju' => $aktor->id_pengguna,
                'disetujui_pada' => now(),
            ]);
        }

        $retur->nominal_pengembalian = $nominalTotal;
        $retur->status_pengembalian_dana = 'menunggu';
        $retur->save();

        return [
            'nominal_refund' => $nominalTotal,
            'detail' => $ringkasanDetail,
            'refund' => $refundTercatat,
        ];
    }
}
