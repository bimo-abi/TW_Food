<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');

            $table->unsignedBigInteger('id_pesanan');

            $table->enum('jenis_pembayaran', [
                'penuh',
                'dp',
                'pelunasan',
                'refund'
            ]);

            $table->string('penyedia_pembayaran', 50)
                ->default('Midtrans');

            $table->string('id_transaksi', 100);

            $table->string('metode_pembayaran', 50)
                ->nullable();

            $table->unsignedInteger('jumlah_pembayaran');

            $table->enum('status_pembayaran', [
                'pending',
                'paid',
                'failed',
                'expired',
                'refunded'
            ])->default('pending');

            $table->timestamp('dibayar_pada')
                ->nullable();

            $table->text('respons_gateway')
                ->nullable();

            $table->timestamps();

            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
