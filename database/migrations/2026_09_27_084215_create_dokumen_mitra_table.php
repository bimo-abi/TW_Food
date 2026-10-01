<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_mitra', function (Blueprint $table) {
            $table->id('id_dokumen_mitra');

            $table->foreignId('id_pengguna')
                ->constrained('pengguna', 'id_pengguna')
                ->cascadeOnDelete();

            $table->enum('jenis_dokumen', [
                'ktp',
                'nib',
                'npwp',
                'dokumen_pendukung'
            ]);

            $table->string('nomor_dokumen', 100)->nullable();

            $table->string('file_dokumen', 255);

            $table->enum('status_verifikasi', [
                'menunggu',
                'disetujui',
                'ditolak'
            ])->default('menunggu');

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_mitra');
    }
};
