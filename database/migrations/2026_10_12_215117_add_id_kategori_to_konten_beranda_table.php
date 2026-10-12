
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konten_beranda', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kategori')->nullable();

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori_konten')
                ->restrictOnDelete();
        });

        DB::statement("
            UPDATE konten_beranda AS kb
            INNER JOIN kategori_konten AS kk
                ON (
                    (kb.jenis_konten = 'informasi_produk'
                        AND kk.slug = 'informasi-produk')
                    OR
                    (kb.jenis_konten = 'informasi_bahan'
                        AND kk.slug = 'informasi-bahan')
                    OR
                    (kb.jenis_konten = 'informasi_merek'
                        AND kk.slug = 'informasi-merek')
                    OR
                    (kb.jenis_konten = 'acara'
                        AND kk.slug = 'acara')
                )
            SET kb.id_kategori = kk.id_kategori
        ");

        $belumTerpetakan = DB::table('konten_beranda')
            ->whereNull('id_kategori')
            ->exists();

        if ($belumTerpetakan) {
            throw new RuntimeException(
                'Ada Konten Beranda yang belum terhubung ke kategori.'
            );
        }

        Schema::table('konten_beranda', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kategori')
                ->nullable(false)
                ->change();

            $table->dropColumn('jenis_konten');
        });
    }

    public function down(): void
    {
        Schema::table('konten_beranda', function (Blueprint $table) {
            $table->enum('jenis_konten', [
                'informasi_produk',
                'informasi_bahan',
                'informasi_merek',
                'acara',
            ])->nullable();
        });

        DB::statement("
            UPDATE konten_beranda AS kb
            INNER JOIN kategori_konten AS kk
                ON kb.id_kategori = kk.id_kategori
            SET kb.jenis_konten = CASE kk.slug
                WHEN 'informasi-produk' THEN 'informasi_produk'
                WHEN 'informasi-bahan' THEN 'informasi_bahan'
                WHEN 'informasi-merek' THEN 'informasi_merek'
                WHEN 'acara' THEN 'acara'
                ELSE NULL
            END
        ");

        Schema::table('konten_beranda', function (Blueprint $table) {
            $table->dropForeign(['id_kategori']);
            $table->dropColumn('id_kategori');
        });
    }
};
