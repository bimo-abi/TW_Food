<?php

namespace Database\Seeders;

use App\Models\KontenBeranda;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TwfoodKontenSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = DB::table('kategori_konten')->pluck('id_kategori', 'slug');

        $idKategori = fn (string $slug) => $kategori[$slug]
            ?? throw new RuntimeException("Slug '{$slug}' tidak ditemukan di kategori_konten.");

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Tentang TWFood'
            ],
            [
                'deskripsi' => 'TWFood merupakan usaha di bidang makanan, FnB, dan oleh-oleh yang menghadirkan produk olahan plant-based dengan tagline Lezat dan Sehat.',
                'gambar' => null,
                'id_kategori' => $idKategori('informasi-merek'),
                'status_aktif' => true,
                'urutan' => 1,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Produk Olahan TWFood'
            ],
            [
                'deskripsi' => 'TWFood menghadirkan berbagai produk olahan berbahan dasar jamur dan bahan pangan lainnya, seperti Kaldu Jamur, Kecap Jamur, Jamur Tiram Krispi, Stik Jantung Pisang, Kerupuk Jamur, Abon Jamur, dan Mie Jamur Tiram Kelor.',
                'gambar' => null,
                'id_kategori' => $idKategori('informasi-produk'),
                'status_aktif' => true,
                'urutan' => 2,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Olahan Jamur Tiram'
            ],
            [
                'deskripsi' => 'Jamur tiram diolah menjadi berbagai produk pangan seperti kaldu, keripik, kecap, abon, kerupuk, dan produk lainnya.',
                'gambar' => null,
                'id_kategori' => $idKategori('informasi-bahan'),
                'status_aktif' => true,
                'urutan' => 3,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Lezat dan Sehat'
            ],
            [
                'deskripsi' => 'TWFood menghadirkan produk plant-based yang mengutamakan cita rasa dan pilihan pangan yang lebih sehat.',
                'gambar' => null,
                'id_kategori' => $idKategori('informasi-merek'),
                'status_aktif' => true,
                'urutan' => 4,
            ]
        );
    }
}
