<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LaporanFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route admin already applies the project's admin middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'id_outlet' => ['nullable', 'integer', 'exists:outlet,id_outlet'],
            'jenis_pembayaran' => ['nullable', 'in:penuh,dp,pelunasan'],
            'status_pesanan' => ['nullable', 'in:menunggu_pembayaran,sedang_diproses,siap_diambil,siap_dikirim,dalam_pengiriman,diterima,dibatalkan'],
            'jenis_pemenuhan' => ['nullable', 'in:reguler,pre_order'],
            'saluran_pesanan' => ['nullable', 'in:online,toko,horeca'],
            'jenis_harga' => ['nullable', 'in:ecer,grosir'],
            'status_refund' => ['nullable', 'in:belum_diajukan,menunggu_diproses,diproses,berhasil,gagal,perlu_diproses_ulang'],
            'jenis_refund' => ['nullable', 'in:pembatalan_operasional,retur'],
            'status_produksi' => ['nullable', 'in:menunggu,diproses,selesai'],
            'jenis_transaksi_keuangan' => ['nullable', 'in:pemasukan,pengeluaran,modal'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();
        $validated['tanggal_mulai'] = $validated['tanggal_mulai'] ?? now()->startOfMonth()->toDateString();
        $validated['tanggal_selesai'] = $validated['tanggal_selesai'] ?? now()->toDateString();

        return $validated;
    }
}
