<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            $table->dropColumn('bukti_retur');
        });
    }

    public function down(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            $table->string('bukti_retur', 255)->nullable();
        });
    }
};
