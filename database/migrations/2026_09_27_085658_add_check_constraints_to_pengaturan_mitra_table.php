<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pengaturan_mitra
            ADD CONSTRAINT chk_minimal_quantity_dp
            CHECK (minimal_quantity_dp > 0)
        ");

        DB::statement("
            ALTER TABLE pengaturan_mitra
            ADD CONSTRAINT chk_persentase_dp
            CHECK (persentase_dp > 0 AND persentase_dp <= 100)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pengaturan_mitra
            DROP CONSTRAINT chk_minimal_quantity_dp
        ");

        DB::statement("
            ALTER TABLE pengaturan_mitra
            DROP CONSTRAINT chk_persentase_dp
        ");
    }
};
