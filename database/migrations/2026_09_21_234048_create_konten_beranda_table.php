<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konten_beranda', function (Blueprint $table) {
            $table->id('id_konten');

            $table->string('judul', 150);

            $table->text('deskripsi')->nullable();

            $table->string('gambar', 255)->nullable();

            $table->enum('jenis_konten', [
                'informasi_produk',
                'informasi_bahan',
                'informasi_merek',
                'acara'
            ]);

            $table->boolean('status_aktif')
                ->default(true);

            $table->unsignedSmallInteger('urutan')
                ->default(0);

            $table->date('tanggal_mulai')
                ->nullable();

            $table->date('tanggal_selesai')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konten_beranda');
    }
};
