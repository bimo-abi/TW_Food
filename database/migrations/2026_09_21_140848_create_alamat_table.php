<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat', function (Blueprint $table) {
            $table->id('id_alamat');

            $table->unsignedBigInteger('id_pengguna');

            $table->string('label', 50);
            $table->string('nama_penerima', 100);
            $table->string('nomor_telepon', 20);
            $table->text('alamat_lengkap');
            $table->string('kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('alamat_utama')->default(false);
            $table->timestamps();
            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat');
    }
};
