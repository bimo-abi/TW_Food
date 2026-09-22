<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resep', function (Blueprint $table) {
            $table->id('id_resep');

            $table->string('judul', 150);

            $table->text('deskripsi')->nullable();

            $table->string('foto', 255)->nullable();

            $table->text('bahan');

            $table->text('langkah_pembuatan');

            $table->unsignedSmallInteger('waktu_memasak')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
