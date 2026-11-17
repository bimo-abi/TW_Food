<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('outlet', 'link_google_maps')) {
            Schema::table('outlet', function (Blueprint $table) {
                $table->string('link_google_maps', 2048)
                    ->nullable()
                    ->after('nomor_telepon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('outlet', 'link_google_maps')) {
            Schema::table('outlet', function (Blueprint $table) {
                $table->dropColumn('link_google_maps');
            });
        }
    }
};
