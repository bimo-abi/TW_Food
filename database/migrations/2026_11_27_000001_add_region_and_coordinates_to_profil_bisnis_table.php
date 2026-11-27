<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_bisnis', function (Blueprint $table) {
            $table->string('kode_provinsi', 2)->nullable();
            $table->string('kode_kabupaten_kota', 5)->nullable();
            $table->string('kode_kecamatan', 8)->nullable();
            $table->string('kode_desa_kelurahan', 13)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profil_bisnis', function (Blueprint $table) {
            $table->dropColumn([
                'kode_provinsi', 'kode_kabupaten_kota', 'kode_kecamatan',
                'kode_desa_kelurahan', 'kabupaten_kota', 'kecamatan',
                'desa_kelurahan', 'latitude', 'longitude',
            ]);
        });
    }
};
