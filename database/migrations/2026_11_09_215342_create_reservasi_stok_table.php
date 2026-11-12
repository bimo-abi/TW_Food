<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasi_stok', function (Blueprint $table) {
            $table->id('id_reservasi_stok');

            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_detail_pesanan');
            $table->unsignedBigInteger('id_varian');

            $table->unsignedSmallInteger('jumlah');

            $table->enum('status_reservasi', [
                'aktif',
                'dikonversi',
                'dilepas',
            ])->default('aktif');

            $table->timestamp('dipesan_pada');
            $table->timestamp('kadaluarsa_pada');

            $table->timestamp('dikonversi_pada')->nullable();
            $table->timestamp('dilepas_pada')->nullable();

            $table->string('alasan_pelepasan', 255)->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->restrictOnDelete();

            $table->foreign('id_detail_pesanan')
                ->references('id_detail_pesanan')
                ->on('detail_pesanan')
                ->restrictOnDelete();

            $table->foreign('id_varian')
                ->references('id_varian')
                ->on('varian_produk')
                ->restrictOnDelete();

            // Index untuk pencarian reservasi
            $table->index(
                ['id_varian', 'status_reservasi', 'kadaluarsa_pada'],
                'reservasi_stok_varian_status_expiry_index'
            );

            $table->index(
                ['id_pesanan', 'status_reservasi'],
                'reservasi_stok_pesanan_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi_stok');
    }
};
