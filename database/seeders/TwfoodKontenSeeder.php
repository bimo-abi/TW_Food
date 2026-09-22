<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KontenBeranda;

class TwfoodKontenSeeder extends Seeder
{
    public function run(): void
    {
        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Tentang TWFood'
            ],
            [
                'deskripsi' => 'TWFood merupakan usaha di bidang makanan, FnB, dan oleh-oleh yang menghadirkan produk olahan plant-based dengan tagline Lezat dan Sehat.',
                'gambar' => null,
                'jenis_konten' => 'informasi_merek',
                'status_aktif' => true,
                'urutan' => 1,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Produk Olahan TWFood'
            ],
            [
                'deskripsi' => 'TWFood menghadirkan berbagai produk olahan berbahan dasar jamur dan bahan pangan lainnya, seperti Kaldu Jamur, Kecap Jamur, Jamur Tiram Krispi, Stik Jantung Pisang, Kerupuk Jamur, Abon Jamur, dan Mie Jamur Tiram Kelor.',
                'gambar' => null,
                'jenis_konten' => 'informasi_produk',
                'status_aktif' => true,
                'urutan' => 2,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Olahan Jamur Tiram'
            ],
            [
                'deskripsi' => 'Jamur tiram diolah menjadi berbagai produk pangan seperti kaldu, keripik, kecap, abon, kerupuk, dan produk lainnya.',
                'gambar' => null,
                'jenis_konten' => 'informasi_bahan',
                'status_aktif' => true,
                'urutan' => 3,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]
        );

        KontenBeranda::updateOrCreate(
            [
                'judul' => 'Lezat dan Sehat'
            ],
            [
                'deskripsi' => 'TWFood menghadirkan produk plant-based yang mengutamakan cita rasa dan pilihan pangan yang lebih sehat.',
                'gambar' => null,
                'jenis_konten' => 'informasi_merek',
                'status_aktif' => true,
                'urutan' => 4,
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
            ]
        );
    }
}
