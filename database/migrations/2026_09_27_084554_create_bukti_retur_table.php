<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_retur', function (Blueprint $table) {
            $table->id('id_bukti_retur');

            $table->foreignId('id_retur')
                ->constrained('retur', 'id_retur')
                ->cascadeOnDelete();

            $table->enum('jenis_media', [
                'foto',
                'video'
            ]);

            $table->string('file_bukti', 255);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_retur');
    }
};
