<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund', function (Blueprint $table) {
            $table->id('id_refund');

            $table->unsignedBigInteger('id_pesanan');
            $table->unsignedBigInteger('id_pembayaran');
            $table->unsignedBigInteger('id_retur')->nullable();

            $table->enum('jenis_refund', [
                'pembatalan_operasional',
                'retur',
            ]);

            $table->unsignedBigInteger('nominal_refund');

            $table->enum('status_refund', [
                'belum_diajukan',
                'menunggu_diproses',
                'diproses',
                'berhasil',
                'gagal',
                'perlu_diproses_ulang',
            ])->default('belum_diajukan');

            $table->text('alasan')->nullable();

            $table->unsignedBigInteger('id_pengguna_penyetuju')
                ->nullable();

            $table->timestamp('disetujui_pada')->nullable();
            $table->timestamp('diproses_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('id_pesanan')
                ->references('id_pesanan')
                ->on('pesanan')
                ->restrictOnDelete();

            $table->foreign('id_pembayaran')
                ->references('id_pembayaran')
                ->on('pembayaran')
                ->restrictOnDelete();

            $table->foreign('id_retur')
                ->references('id_retur')
                ->on('retur')
                ->restrictOnDelete();

            $table->foreign('id_pengguna_penyetuju')
                ->references('id_pengguna')
                ->on('pengguna')
                ->restrictOnDelete();

            // Index
            $table->index(
                ['id_pesanan', 'status_refund'],
                'refund_pesanan_status_index'
            );

            $table->index(
                ['id_pembayaran', 'status_refund'],
                'refund_pembayaran_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund');
    }
};
