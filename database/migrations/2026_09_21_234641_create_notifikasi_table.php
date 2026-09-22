<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id('id_notifikasi');

            $table->unsignedBigInteger('id_pengguna');

            $table->string('judul', 150);

            $table->text('pesan');

            $table->string('jenis', 50);

            $table->unsignedBigInteger('id_referensi')->nullable();

            $table->boolean('sudah_dibaca')
                ->default(false);

            $table->timestamp('created_at')
                ->useCurrent();

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
