<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alamat', function (Blueprint $table) {
            $table->string('kode_provinsi', 2)->nullable();
            $table->string('kode_kabupaten_kota', 5)->nullable();
            $table->string('kode_kecamatan', 8)->nullable();
            $table->string('kode_desa_kelurahan', 13)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('kode_provinsi_saat_pesan', 2)->nullable();
            $table->string('kode_kabupaten_kota_saat_pesan', 5)->nullable();
            $table->string('kode_kecamatan_saat_pesan', 8)->nullable();
            $table->string('kode_desa_kelurahan_saat_pesan', 13)->nullable();
            $table->string('kabupaten_kota_saat_pesan', 100)->nullable();
            $table->string('kecamatan_saat_pesan', 100)->nullable();
            $table->string('desa_kelurahan_saat_pesan', 100)->nullable();
            $table->decimal('latitude_saat_pesan', 10, 8)->nullable();
            $table->decimal('longitude_saat_pesan', 11, 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn([
                'kode_provinsi_saat_pesan',
                'kode_kabupaten_kota_saat_pesan',
                'kode_kecamatan_saat_pesan',
                'kode_desa_kelurahan_saat_pesan',
                'kabupaten_kota_saat_pesan',
                'kecamatan_saat_pesan',
                'desa_kelurahan_saat_pesan',
                'latitude_saat_pesan',
                'longitude_saat_pesan',
            ]);
        });

        Schema::table('alamat', function (Blueprint $table) {
            $table->dropColumn([
                'kode_provinsi',
                'kode_kabupaten_kota',
                'kode_kecamatan',
                'kode_desa_kelurahan',
                'kabupaten_kota',
                'kecamatan',
                'desa_kelurahan',
            ]);
        });
    }
};
