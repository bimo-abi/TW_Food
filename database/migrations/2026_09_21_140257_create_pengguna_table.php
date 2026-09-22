<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id('id_pengguna');

            $table->string('nama', 100);
            $table->string('email', 150)->unique();
            $table->string('kata_sandi', 255);
            $table->string('nomor_telepon', 20);

            $table->enum('peran', [
                'pelanggan',
                'mitra'
            ]);

            $table->enum('jenis_pelanggan', [
                'umum',
                'toko',
                'horeca'
            ])->nullable();

            $table->string('foto_profil', 255)->nullable();

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
