<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            if (!Schema::hasColumn('retur', 'metode_pengembalian')) {
                $table->string('metode_pengembalian', 20)->nullable()->after('alasan_penolakan');
            }

            if (!Schema::hasColumn('retur', 'instruksi_pengembalian')) {
                $table->text('instruksi_pengembalian')->nullable()->after('metode_pengembalian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('retur', 'instruksi_pengembalian')) {
                $columns[] = 'instruksi_pengembalian';
            }
            if (Schema::hasColumn('retur', 'metode_pengembalian')) {
                $columns[] = 'metode_pengembalian';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
