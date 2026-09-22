<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_bisnis', function (Blueprint $table) {
            $table->id('id_profil_bisnis');

            $table->unsignedBigInteger('id_pengguna')->unique();

            $table->enum('jenis_bisnis', [
                'toko',
                'horeca'
            ]);

            $table->string('nama_bisnis', 150);
            $table->string('nama_pic', 100);
            $table->string('nomor_telepon_bisnis', 20);

            $table->text('alamat_bisnis');

            $table->string('kota', 100);
            $table->string('provinsi', 100);
            $table->string('kode_pos', 10);

            $table->string('nib', 100)->nullable();
            $table->string('npwp', 100)->nullable();

            $table->timestamps();

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_bisnis');
    }
};
