<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_mitra', function (Blueprint $table) {
            $table->id('id_pengaturan');

            $table->unsignedBigInteger('id_pengguna')->unique();

            $table->unsignedSmallInteger('minimal_quantity_dp')
                ->default(10);

            $table->decimal('persentase_dp', 5, 2)
                ->default(50.00);

            $table->boolean('notifikasi_aktif')
                ->default(false);

            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_mitra');
    }
};
