<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promosi', function (Blueprint $table) {
            $table->id('id_promosi');

            $table->string('nama_promosi', 150);

            $table->text('deskripsi')->nullable();

            $table->enum('jenis_promosi', [
                'diskon_persen',
                'diskon_nominal',
                'harga_khusus'
            ]);

            $table->decimal('nilai_persen', 5, 2)->nullable();

            $table->unsignedMediumInteger('nilai_nominal')
                ->nullable();

            $table->unsignedSmallInteger('minimal_pembelian')
                ->nullable();

            $table->date('tanggal_mulai');

            $table->date('tanggal_selesai');

            $table->boolean('status_aktif')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promosi');
    }
};  
