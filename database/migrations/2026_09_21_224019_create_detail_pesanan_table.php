<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id('id_detail_pesanan');

            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_varian');

            $table->string('nama_produk_saat_pesan', 150);
            $table->string('nama_varian_saat_pesan', 100);

            $table->unsignedMediumInteger('harga_saat_pesan');

            $table->unsignedSmallInteger('jumlah');

            $table->unsignedInteger('subtotal');

            $table->timestamps();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->onDelete('cascade');

            $table->foreign('id_varian')
                ->references('id_varian')
                ->on('varian_produk')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
