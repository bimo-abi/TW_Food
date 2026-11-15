<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('detail_retur', 'jumlah_layak_dijual')) {
            Schema::table('detail_retur', function (Blueprint $table) {
                $table->unsignedSmallInteger('jumlah_layak_dijual')
                    ->default(0)
                    ->after('jumlah_disetujui');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('detail_retur', 'jumlah_layak_dijual')) {
            Schema::table('detail_retur', function (Blueprint $table) {
                $table->dropColumn('jumlah_layak_dijual');
            });
        }
    }
};
