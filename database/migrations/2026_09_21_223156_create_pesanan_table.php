<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');

            $table->string('nomor_pesanan', 50)->unique();

            $table->unsignedBigInteger('id_pengguna');

            $table->unsignedBigInteger('id_alamat')->nullable();
            $table->unsignedBigInteger('id_outlet')->nullable();

            $table->enum('saluran_pesanan', [
                'online',
                'toko',
                'horeca'
            ]);

            $table->enum('jenis_pesanan', [
                'pickup',
                'delivery'
            ]);

            $table->unsignedInteger('subtotal_produk');

            $table->unsignedInteger('diskon')->default(0);

            $table->unsignedInteger('total_pesanan');

            $table->enum('status_pembayaran', [
                'belum_dibayar',
                'dp_dibayar',
                'lunas',
                'gagal',
                'kadaluarsa',
                'dikembalikan'
            ])->default('belum_dibayar');

            $table->enum('status_pesanan', [
                'pesanan_diterima',
                'sedang_diproses',
                'pesanan_siap',
                'menunggu_pengiriman',
                'dalam_pengiriman',
                'diterima',
                'siap_diambil',
                'pesanan_diambil',
                'selesai',
                'dibatalkan'
            ])->default('pesanan_diterima');

            $table->enum('kurir', [
                'gosend',
                'jnt'
            ])->nullable();

            $table->string('nomor_resi', 100)->nullable();

            $table->string('tautan_pelacakan', 255)->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('restrict');

            $table->foreign('id_alamat')
                ->references('id_alamat')
                ->on('alamat')
                ->onDelete('set null');

            $table->foreign('id_outlet')
                ->references('id_outlet')
                ->on('outlet')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
