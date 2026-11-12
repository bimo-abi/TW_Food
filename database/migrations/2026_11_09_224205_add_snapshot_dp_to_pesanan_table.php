<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->boolean('menggunakan_dp')
                ->default(false);

            $table->unsignedInteger('dasar_perhitungan_dp')
                ->nullable();

            $table->decimal('persentase_dp', 5, 2)
                ->nullable();

            $table->unsignedInteger('nominal_dp')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn([
                'menggunakan_dp',
                'dasar_perhitungan_dp',
                'persentase_dp',
                'nominal_dp',
            ]);
        });
    }
};
