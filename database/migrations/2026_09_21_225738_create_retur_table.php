<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur', function (Blueprint $table) {
            $table->id('id_retur');

            $table->unsignedBigInteger('id_pesanan');

            $table->string('nomor_retur', 50)->unique();

            $table->text('alasan_retur');

            $table->string('bukti_retur', 255)->nullable();

            $table->enum('status_retur', [
                'diajukan',
                'diproses',
                'disetujui',
                'ditolak',
                'selesai'
            ])->default('diajukan');

            $table->text('catatan_mitra')->nullable();

            $table->enum('status_pengembalian_dana', [
                'tidak_ada',
                'menunggu',
                'diproses',
                'selesai',
                'gagal'
            ])->default('tidak_ada');

            $table->unsignedInteger('nominal_pengembalian')
                ->default(0);

            $table->timestamp('diajukan_pada');

            $table->timestamps();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur');
    }
};
