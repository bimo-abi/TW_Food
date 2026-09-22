<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlet', function (Blueprint $table) {
            $table->id('id_outlet');

            $table->string('nama_outlet', 150);

            $table->text('alamat');

            $table->string('nomor_telepon', 20)->nullable();

            $table->decimal('latitude', 10, 8)->nullable();

            $table->decimal('longitude', 11, 8)->nullable();

            $table->time('jam_buka')->nullable();

            $table->time('jam_tutup')->nullable();

            $table->text('deskripsi')->nullable();

            $table->string('foto', 255)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlet');
    }
};
