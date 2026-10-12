<?php

namespace Database\Seeders;

use App\Models\KategoriKonten;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KategoriKontenSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            [
                'nama_kategori' => 'Informasi Produk',
                'deskripsi' => 'Informasi mengenai produk TWFOOD.',
            ],
            [
                'nama_kategori' => 'Informasi Bahan',
                'deskripsi' => 'Informasi bahan baku dan kandungan produk.',
            ],
            [
                'nama_kategori' => 'Informasi Merek',
                'deskripsi' => 'Informasi profil dan identitas merek TWFOOD.',
            ],
            [
                'nama_kategori' => 'Acara',
                'deskripsi' => 'Informasi kegiatan dan acara TWFOOD.',
            ],
        ];

        foreach ($kategori as $item) {
            KategoriKonten::firstOrCreate(
                ['nama_kategori' => $item['nama_kategori']],
                [
                    'slug' => Str::slug($item['nama_kategori']),
                    'deskripsi' => $item['deskripsi'],
                    'status_aktif' => true,
                ]
            );
        }
    }
}
