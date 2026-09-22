<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_keranjang', function (Blueprint $table) {
            $table->id('id_detail_keranjang');

            $table->unsignedBigInteger('id_keranjang');
            $table->unsignedBigInteger('id_varian');

            $table->unsignedSmallInteger('jumlah');

            $table->timestamps();

            $table->unique([
                'id_keranjang',
                'id_varian'
            ]);

            $table->foreign('id_keranjang')
                ->references('id_keranjang')
                ->on('keranjang')
                ->onDelete('cascade');

            $table->foreign('id_varian')
                ->references('id_varian')
                ->on('varian_produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_keranjang');
    }
};
