<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('produksi_pre_order', function (Blueprint $table) {
            $table->id('id_produksi_pre_order');

            $table->unsignedBigInteger('id_detail_pesanan');
            $table->unsignedInteger('jumlah_produksi');

            $table->enum('status_produksi', [
                'menunggu',
                'diproses',
                'selesai',
            ])->default('menunggu');

            $table->timestamps();

            $table->foreign('id_detail_pesanan')
                ->references('id_detail_pesanan')
                ->on('detail_pesanan')
                ->cascadeOnDelete();

            $table->unique('id_detail_pesanan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_pre_order');
    }
};
