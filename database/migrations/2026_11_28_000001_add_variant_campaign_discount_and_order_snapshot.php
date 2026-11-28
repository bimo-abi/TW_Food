<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promosi_varian', function (Blueprint $table) {
            if (!Schema::hasColumn('promosi_varian', 'nilai_persen')) {
                $table->decimal('nilai_persen', 5, 2)->nullable()->after('id_varian');
            }
        });

        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_pesanan', 'id_promosi_saat_pesan')) {
                $table->unsignedBigInteger('id_promosi_saat_pesan')->nullable();
            }
            if (!Schema::hasColumn('detail_pesanan', 'nama_promosi_saat_pesan')) {
                $table->string('nama_promosi_saat_pesan', 150)->nullable();
            }
            if (!Schema::hasColumn('detail_pesanan', 'nilai_persen_diskon_saat_pesan')) {
                $table->decimal('nilai_persen_diskon_saat_pesan', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('detail_pesanan', 'id_promosi_saat_pesan') ? 'id_promosi_saat_pesan' : null,
                Schema::hasColumn('detail_pesanan', 'nama_promosi_saat_pesan') ? 'nama_promosi_saat_pesan' : null,
                Schema::hasColumn('detail_pesanan', 'nilai_persen_diskon_saat_pesan') ? 'nilai_persen_diskon_saat_pesan' : null,
            ]);
            if ($columns) $table->dropColumn(array_values($columns));
        });

        Schema::table('promosi_varian', function (Blueprint $table) {
            if (Schema::hasColumn('promosi_varian', 'nilai_persen')) {
                $table->dropColumn('nilai_persen');
            }
        });
    }
};
