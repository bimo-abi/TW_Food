<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_aktivitas_transaksi', function (Blueprint $table) {
            $table->id('id_riwayat_aktivitas');

            $table->unsignedBigInteger('id_pengguna')->nullable();
            $table->string('jenis_aktor', 20);

            $table->unsignedBigInteger('id_pesanan')->nullable();
            $table->unsignedBigInteger('id_pembayaran')->nullable();

            $table->string('jenis_aktivitas', 100);
            $table->string('status_sebelum', 50)->nullable();
            $table->string('status_sesudah', 50)->nullable();
            $table->text('deskripsi');

            $table->timestamp('created_at')->useCurrent();

            // Foreign key
            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->nullOnDelete();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->nullOnDelete();

            $table->foreign('id_pembayaran')
                ->references('id_pembayaran')
                ->on('pembayaran')
                ->nullOnDelete();

            // Index untuk pencarian riwayat
            $table->index('jenis_aktor');
            $table->index('jenis_aktivitas');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_aktivitas_transaksi');
    }
};
