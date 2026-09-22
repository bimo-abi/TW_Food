<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('varian_produk', function (Blueprint $table) {
            $table->id('id_varian');

            $table->unsignedBigInteger('id_produk');

            $table->string('nama_varian', 100);

            $table->unsignedSmallInteger('berat_gram')->nullable();

            $table->string('satuan_jual', 20);

            $table->unsignedMediumInteger('stok')->default(0);

            $table->boolean('tersedia_pre_order')->default(false);

            $table->date('tanggal_mulai_pre_order')->nullable();
            $table->date('tanggal_selesai_pre_order')->nullable();
            $table->date('estimasi_tersedia')->nullable();

            $table->boolean('status_aktif')->default(true);

            $table->timestamps();

            $table->unique([
                'id_produk',
                'nama_varian'
            ]);

            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('varian_produk');
    }
};
