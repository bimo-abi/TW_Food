<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_mitra', function (Blueprint $table) {
            $table->boolean('dp_aktif')
                ->default(true)
                ->after('persentase_dp');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_mitra', function (Blueprint $table) {
            $table->dropColumn('dp_aktif');
        });
    }
};
