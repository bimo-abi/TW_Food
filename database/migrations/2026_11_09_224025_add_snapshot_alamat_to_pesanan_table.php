<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('nama_penerima_saat_pesan', 100)
                ->nullable();

            $table->string('nomor_telepon_saat_pesan', 20)
                ->nullable();

            $table->text('alamat_lengkap_saat_pesan')
                ->nullable();

            $table->string('kota_saat_pesan', 100)
                ->nullable();

            $table->string('provinsi_saat_pesan', 100)
                ->nullable();

            $table->string('kode_pos_saat_pesan', 10)
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn([
                'nama_penerima_saat_pesan',
                'nomor_telepon_saat_pesan',
                'alamat_lengkap_saat_pesan',
                'kota_saat_pesan',
                'provinsi_saat_pesan',
                'kode_pos_saat_pesan',
            ]);
        });
    }
};
