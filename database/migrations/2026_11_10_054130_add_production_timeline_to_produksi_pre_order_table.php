<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'estimasi_selesai' => fn ($table) =>
                $table->timestamp('estimasi_selesai')->nullable(),

            'mulai_produksi_pada' => fn ($table) =>
                $table->timestamp('mulai_produksi_pada')->nullable(),

            'selesai_produksi_pada' => fn ($table) =>
                $table->timestamp('selesai_produksi_pada')->nullable(),

            'catatan_produksi' => fn ($table) =>
                $table->text('catatan_produksi')->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn('produksi_pre_order', $column)) {
                Schema::table('produksi_pre_order', $definition);
            }
        }
    }

    public function down(): void
    {
        $columns = [
            'estimasi_selesai',
            'mulai_produksi_pada',
            'selesai_produksi_pada',
            'catatan_produksi',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('produksi_pre_order', $column)) {
                Schema::table('produksi_pre_order', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
