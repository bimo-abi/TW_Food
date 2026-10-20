<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_mitra', function (Blueprint $table) {
            $table->unsignedInteger('minimal_nominal_dp')
                ->nullable()
                ->after('minimal_quantity_dp');
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_mitra', function (Blueprint $table) {
            $table->dropColumn('minimal_nominal_dp');
        });
    }
};
