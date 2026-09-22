<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daftar_harga', function (Blueprint $table) {
            $table->id('id_daftar_harga');

            $table->unsignedBigInteger('id_varian');

            $table->enum('jenis_harga', [
                'ecer',
                'grosir'
            ]);

            $table->unsignedMediumInteger('harga');

            $table->unsignedSmallInteger('minimal_pembelian')
                ->default(1);

            $table->string('satuan_minimal', 20)->nullable();

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();

            $table->unique([
                'id_varian',
                'jenis_harga'
            ]);

            $table->foreign('id_varian')
                ->references('id_varian')
                ->on('varian_produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daftar_harga');
    }
};
