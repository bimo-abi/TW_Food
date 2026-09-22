<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promosi_varian', function (Blueprint $table) {
            $table->id('id_promosi_varian');

            $table->unsignedBigInteger('id_promosi');

            $table->unsignedBigInteger('id_varian');

            $table->timestamps();

            $table->unique([
                'id_promosi',
                'id_varian'
            ]);

            $table->foreign('id_promosi')
                ->references('id_promosi')
                ->on('promosi')
                ->onDelete('cascade');

            $table->foreign('id_varian')
                ->references('id_varian')
                ->on('varian_produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promosi_varian');
    }
};
