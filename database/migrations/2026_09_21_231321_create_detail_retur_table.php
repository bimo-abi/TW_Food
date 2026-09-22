<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_retur', function (Blueprint $table) {
            $table->id('id_detail_retur');

            $table->unsignedBigInteger('id_retur');

            $table->unsignedBigInteger('id_detail_pesanan');

            $table->unsignedSmallInteger('jumlah_diretur');

            $table->text('alasan_item')->nullable();

            $table->unsignedInteger('nominal_refund');

            $table->timestamps();

            $table->foreign('id_retur')
                ->references('id_retur')
                ->on('retur')
                ->onDelete('cascade');

            $table->foreign('id_detail_pesanan')
                ->references('id_detail_pesanan')
                ->on('detail_pesanan')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_retur');
    }
};
