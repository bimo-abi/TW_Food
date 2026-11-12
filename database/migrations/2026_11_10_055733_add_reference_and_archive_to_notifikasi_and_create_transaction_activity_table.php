<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->string('jenis_referensi', 50)->nullable();
            $table->timestamp('diarsipkan_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_referensi',
                'diarsipkan_pada',
            ]);
        });
    }
};
