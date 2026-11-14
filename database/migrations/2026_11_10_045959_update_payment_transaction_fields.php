<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'kunci_idempotensi')) {
                $table->string('kunci_idempotensi', 100)
                    ->nullable()
                    ->unique();
            }

            if (!Schema::hasColumn('pembayaran', 'waktu_kadaluarsa')) {
                $table->timestamp('waktu_kadaluarsa')
                    ->nullable();
            }

            if (!Schema::hasColumn('pembayaran', 'dikonfirmasi_oleh')) {
                $table->unsignedBigInteger('dikonfirmasi_oleh')
                    ->nullable();
            }

            if (!Schema::hasColumn('pembayaran', 'dikonfirmasi_pada')) {
                $table->timestamp('dikonfirmasi_pada')
                    ->nullable();
            }
        });

        DB::statement("
            ALTER TABLE pembayaran
            MODIFY id_transaksi VARCHAR(100) NULL
        ");
    }

    public function down(): void
    {
        // Rollback hanya aman jika seluruh id_transaksi sudah terisi.
        if (DB::table('pembayaran')->whereNull('id_transaksi')->exists()) {
            throw new RuntimeException(
                'Rollback tidak dapat dilakukan karena terdapat id_transaksi NULL.'
            );
        }

        DB::statement("
            ALTER TABLE pembayaran
            MODIFY id_transaksi VARCHAR(100) NOT NULL
        ");

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropUnique(['kunci_idempotensi']);

            $table->dropColumn([
                'kunci_idempotensi',
                'waktu_kadaluarsa',
                'dikonfirmasi_oleh',
                'dikonfirmasi_pada',
            ]);
        });
    }
};
