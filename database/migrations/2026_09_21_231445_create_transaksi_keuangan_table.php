<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_keuangan', function (Blueprint $table) {
            $table->id('id_transaksi_keuangan');

            $table->unsignedBigInteger('id_pengguna');

            $table->enum('jenis_transaksi', [
                'pemasukan',
                'pengeluaran',
                'modal'
            ]);

            $table->string('kategori', 100);

            $table->text('keterangan')->nullable();

            $table->unsignedInteger('nominal');

            $table->date('tanggal_transaksi');

            $table->string('bukti_transaksi', 255)->nullable();

            $table->timestamps();

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangan');
    }
};
