<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('percobaan_refund', function (Blueprint $table) {
            $table->id('id_percobaan_refund');

            $table->unsignedBigInteger('id_refund');

            $table->string('kunci_percobaan', 100)->unique();

            $table->string('penyedia_pembayaran', 50);

            $table->unsignedBigInteger('nominal_refund');

            $table->enum('status_percobaan', [
                'menunggu',
                'berhasil',
                'gagal',
            ])->default('menunggu');

            $table->string('referensi_gateway', 100)->nullable();

            $table->json('respons_gateway')->nullable();

            $table->text('pesan_error')->nullable();

            $table->timestamp('dimulai_pada');
            $table->timestamp('selesai_pada')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('id_refund')
                ->references('id_refund')
                ->on('refund')
                ->restrictOnDelete();

            // Index
            $table->index(
                ['id_refund', 'created_at'],
                'percobaan_refund_refund_created_index'
            );

            $table->index(
                ['status_percobaan', 'dimulai_pada'],
                'percobaan_refund_status_waktu_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('percobaan_refund');
    }
};
