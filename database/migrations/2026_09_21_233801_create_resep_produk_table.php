<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resep_produk', function (Blueprint $table) {
            $table->id('id_resep_produk');

            $table->unsignedBigInteger('id_resep');

            $table->unsignedBigInteger('id_produk');

            $table->string('jumlah', 50)->nullable();

            $table->timestamps();

            $table->unique([
                'id_resep',
                'id_produk'
            ]);

            $table->foreign('id_resep')
                ->references('id_resep')
                ->on('resep')
                ->onDelete('cascade');

            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('produk')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resep_produk');
    }
};
