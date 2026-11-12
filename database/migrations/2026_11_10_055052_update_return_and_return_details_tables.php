<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom tambahan tabel retur
        Schema::table('retur', function (Blueprint $table) {
            if (!Schema::hasColumn('retur', 'disetujui_pada')) {
                $table->timestamp('disetujui_pada')->nullable();
            }

            if (!Schema::hasColumn('retur', 'barang_diterima_pada')) {
                $table->timestamp('barang_diterima_pada')->nullable();
            }

            if (!Schema::hasColumn('retur', 'diperiksa_pada')) {
                $table->timestamp('diperiksa_pada')->nullable();
            }

            if (!Schema::hasColumn('retur', 'selesai_pada')) {
                $table->timestamp('selesai_pada')->nullable();
            }

            if (!Schema::hasColumn('retur', 'alasan_penolakan')) {
                $table->text('alasan_penolakan')->nullable();
            }
        });

        // Pertahankan status lama sambil menambahkan status baru.
        DB::statement("
            ALTER TABLE retur
            MODIFY status_retur ENUM(
                'diajukan',
                'diproses',
                'disetujui',
                'ditolak',
                'selesai',
                'menunggu_persetujuan',
                'menunggu_pengembalian_barang',
                'barang_diterima',
                'sedang_diperiksa',
                'dibatalkan'
            ) NOT NULL DEFAULT 'diajukan'
        ");

        // Kolom tambahan tabel detail_retur
        Schema::table('detail_retur', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_retur', 'jumlah_diminta')) {
                $table->unsignedSmallInteger('jumlah_diminta')->nullable();
            }

            if (!Schema::hasColumn('detail_retur', 'jumlah_disetujui')) {
                $table->unsignedSmallInteger('jumlah_disetujui')->nullable();
            }

            if (!Schema::hasColumn('detail_retur', 'diskon_dialokasikan')) {
                $table->unsignedInteger('diskon_dialokasikan')->default(0);
            }

            if (!Schema::hasColumn('detail_retur', 'status_kelayakan_stok')) {
                $table->enum('status_kelayakan_stok', [
                    'belum_diperiksa',
                    'layak_dijual',
                    'tidak_layak',
                ])->default('belum_diperiksa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('retur', function (Blueprint $table) {
            $table->dropColumn([
                'disetujui_pada',
                'barang_diterima_pada',
                'diperiksa_pada',
                'selesai_pada',
                'alasan_penolakan',
            ]);
        });

        Schema::table('detail_retur', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_diminta',
                'jumlah_disetujui',
                'diskon_dialokasikan',
                'status_kelayakan_stok',
            ]);
        });

        // Enum status sengaja tidak dipersempit saat rollback
        // untuk menghindari kegagalan jika status baru sudah digunakan.
    }
};
