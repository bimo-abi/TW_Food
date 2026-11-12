<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pesanan
            MODIFY saluran_pesanan
            ENUM('online', 'toko', 'horeca')
            NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pesanan
            MODIFY saluran_pesanan
            ENUM('online', 'toko')
            NOT NULL
        ");
    }
};
