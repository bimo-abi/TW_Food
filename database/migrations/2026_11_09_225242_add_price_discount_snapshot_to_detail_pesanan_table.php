<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_pesanan', 'jenis_harga_saat_pesan')) {
                $table->enum('jenis_harga_saat_pesan', ['ecer', 'grosir'])
                    ->nullable();
            }

            if (!Schema::hasColumn('detail_pesanan', 'diskon_item')) {
                $table->unsignedInteger('diskon_item')
                    ->default(0);
            }

            if (!Schema::hasColumn('detail_pesanan', 'subtotal_setelah_diskon')) {
                $table->unsignedInteger('subtotal_setelah_diskon')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'jenis_harga_saat_pesan',
                'diskon_item',
                'subtotal_setelah_diskon',
            ] as $column) {
                if (Schema::hasColumn('detail_pesanan', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
