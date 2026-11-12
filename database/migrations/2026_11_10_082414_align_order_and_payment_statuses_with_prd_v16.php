<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Tahap 1: Perluas enum sementara agar status lama
         * dan status baru dapat diproses tanpa kehilangan data.
         */
        DB::statement("
            ALTER TABLE pesanan
            MODIFY status_pesanan ENUM(
                'pesanan_diterima',
                'sedang_diproses',
                'pesanan_siap',
                'menunggu_pengiriman',
                'dalam_pengiriman',
                'diterima',
                'siap_diambil',
                'pesanan_diambil',
                'selesai',
                'dibatalkan',
                'menunggu_pembayaran',
                'siap_dikirim'
            ) NOT NULL DEFAULT 'menunggu_pembayaran'
        ");

        DB::statement("
            ALTER TABLE pesanan
            MODIFY status_pembayaran ENUM(
                'belum_dibayar',
                'menunggu_pembayaran',
                'dp_dibayar',
                'sebagian_dibayar',
                'lunas',
                'gagal',
                'kadaluarsa',
                'dibatalkan',
                'dikembalikan'
            ) NOT NULL DEFAULT 'belum_dibayar'
        ");

        /*
         * Tahap 2: Petakan status pesanan lama.
         */
        DB::statement("
            UPDATE pesanan
            SET status_pesanan = CASE
                WHEN status_pesanan = 'pesanan_diterima'
                    AND status_pembayaran IN (
                        'lunas',
                        'dp_dibayar',
                        'sebagian_dibayar'
                    )
                    THEN 'sedang_diproses'

                WHEN status_pesanan = 'pesanan_diterima'
                    THEN 'menunggu_pembayaran'

                WHEN status_pesanan = 'pesanan_siap'
                    AND jenis_pesanan = 'delivery'
                    THEN 'siap_dikirim'

                WHEN status_pesanan = 'pesanan_siap'
                    AND jenis_pesanan = 'pickup'
                    THEN 'siap_diambil'

                WHEN status_pesanan = 'menunggu_pengiriman'
                    AND jenis_pesanan = 'delivery'
                    THEN 'siap_dikirim'

                WHEN status_pesanan = 'menunggu_pengiriman'
                    AND jenis_pesanan = 'pickup'
                    THEN 'siap_diambil'

                WHEN status_pesanan IN (
                    'pesanan_diambil',
                    'selesai'
                )
                    THEN 'diterima'

                ELSE status_pesanan
            END
        ");

        /*
         * Tahap 3: Pastikan seluruh status sudah sesuai
         * sebelum menghapus pilihan enum lama.
         */
        $statusLama = DB::table('pesanan')
            ->whereIn('status_pesanan', [
                'pesanan_diterima',
                'pesanan_siap',
                'menunggu_pengiriman',
                'pesanan_diambil',
                'selesai',
            ])
            ->exists();

        if ($statusLama) {
            throw new RuntimeException(
                'Masih terdapat status pesanan lama yang belum dipetakan.'
            );
        }

        /*
         * Tahap 4: Tetapkan enum final PRD v1.6.
         */
        DB::statement("
            ALTER TABLE pesanan
            MODIFY status_pesanan ENUM(
                'menunggu_pembayaran',
                'sedang_diproses',
                'siap_diambil',
                'siap_dikirim',
                'dalam_pengiriman',
                'diterima',
                'dibatalkan'
            ) NOT NULL DEFAULT 'menunggu_pembayaran'
        ");
    }

    public function down(): void
    {
        throw new RuntimeException(
            'Rollback otomatis dinonaktifkan untuk menjaga data status transaksi. '
            . 'Gunakan migration koreksi baru jika diperlukan.'
        );
    }
};
