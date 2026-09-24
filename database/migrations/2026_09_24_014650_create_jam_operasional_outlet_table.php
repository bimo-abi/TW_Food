<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jam_operasional_outlet', function (Blueprint $table) {
            $table->id('id_jam_operasional');

            $table->foreignId('id_outlet')
                ->constrained('outlet', 'id_outlet')
                ->cascadeOnDelete();

            $table->enum('hari', [
                'senin',
                'selasa',
                'rabu',
                'kamis',
                'jumat',
                'sabtu',
                'minggu'
            ]);

            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();

            $table->boolean('tutup')->default(false);

            $table->timestamps();

            $table->unique(['id_outlet', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jam_operasional_outlet');
    }
};
