<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Resep;
use App\Models\Produk;

class TwfoodResepSeeder extends Seeder
{
    public function run(): void
    {
      //ambil produk
        $kaldu = Produk::where(
            'nama_produk',
            'Kaldu Jamur Tiram dan Shitake'
        )->first();

        $mie = Produk::where(
            'nama_produk',
            'Mie Instan Jamur Tiram'
        )->first();

        $jamurKrispi = Produk::where(
            'nama_produk',
            'Jamur Tiram Krispi'
        )->first();

        $abon = Produk::where(
            'nama_produk',
            'Abon Jamur Tiram'
        )->first();


        /*
        |--------------------------------------------------------------------------
        | 2. Resep Nasi Goreng Jamur
        |--------------------------------------------------------------------------
        */

        $resep = Resep::updateOrCreate(
            [
                'judul' => 'Nasi Goreng Jamur'
            ],
            [
                'deskripsi' =>
                'Nasi goreng sederhana dengan tambahan bumbu kaldu jamur untuk memberikan cita rasa gurih.',

                'foto' => null,

                'bahan' =>
                "2 porsi nasi putih\n" .
                    "1 butir telur\n" .
                    "1 batang daun bawang\n" .
                    "1 siung bawang putih\n" .
                    "1 sdm minyak goreng\n" .
                    "1/2 sdt Kaldu Jamur Tiram dan Shitake\n" .
                    "Garam secukupnya",

                'langkah_pembuatan' =>
                "1. Panaskan minyak dalam wajan.\n" .
                    "2. Tumis bawang putih hingga harum.\n" .
                    "3. Masukkan telur lalu orak-arik.\n" .
                    "4. Masukkan nasi putih.\n" .
                    "5. Tambahkan Kaldu Jamur Tiram dan Shitake serta garam.\n" .
                    "6. Aduk hingga semua bahan tercampur rata.\n" .
                    "7. Tambahkan daun bawang.\n" .
                    "8. Masak hingga matang dan sajikan.",

                'waktu_memasak' => 20,
            ]
        );


        if ($kaldu) {
            $resep->produk()->updateOrCreate(
                [
                    'id_produk' => $kaldu->id_produk
                ],
                [
                    'jumlah' => '1/2 sdt'
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 3. Mie Goreng Jamur Tiram
        |--------------------------------------------------------------------------
        */

        $resep = Resep::updateOrCreate(
            [
                'judul' => 'Mie Goreng Jamur Tiram'
            ],
            [
                'deskripsi' =>
                'Olahan mie sederhana dengan tambahan sayuran dan bumbu untuk sajian praktis.',

                'foto' => null,

                'bahan' =>
                "1 bungkus Mie Instan Jamur Tiram\n" .
                    "1 butir telur\n" .
                    "1 batang daun bawang\n" .
                    "1 siung bawang putih\n" .
                    "Sayuran secukupnya\n" .
                    "Minyak goreng secukupnya",

                'langkah_pembuatan' =>
                "1. Rebus Mie Instan Jamur Tiram hingga matang.\n" .
                    "2. Tiriskan mie.\n" .
                    "3. Tumis bawang putih hingga harum.\n" .
                    "4. Masukkan telur dan orak-arik.\n" .
                    "5. Tambahkan sayuran.\n" .
                    "6. Masukkan mie dan aduk rata.\n" .
                    "7. Tambahkan bumbu sesuai selera.\n" .
                    "8. Masak sebentar lalu sajikan.",

                'waktu_memasak' => 15,
            ]
        );


        if ($mie) {
            $resep->produk()->updateOrCreate(
                [
                    'id_produk' => $mie->id_produk
                ],
                [
                    'jumlah' => '1 bungkus'
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 4. Jamur Tiram Krispi Pedas
        |--------------------------------------------------------------------------
        */

        $resep = Resep::updateOrCreate(
            [
                'judul' => 'Jamur Tiram Krispi Pedas'
            ],
            [
                'deskripsi' =>
                'Camilan praktis dengan Jamur Tiram Krispi yang dipadukan dengan bumbu pedas.',

                'foto' => null,

                'bahan' =>
                "1 bungkus Jamur Tiram Krispi\n" .
                    "Cabai bubuk secukupnya\n" .
                    "Daun jeruk secukupnya\n" .
                    "Sedikit minyak goreng",

                'langkah_pembuatan' =>
                "1. Siapkan Jamur Tiram Krispi.\n" .
                    "2. Panaskan sedikit minyak.\n" .
                    "3. Tumis daun jeruk sebentar.\n" .
                    "4. Masukkan Jamur Tiram Krispi.\n" .
                    "5. Tambahkan cabai bubuk sesuai selera.\n" .
                    "6. Aduk hingga bumbu merata.\n" .
                    "7. Sajikan sebagai camilan.",

                'waktu_memasak' => 10,
            ]
        );


        if ($jamurKrispi) {
            $resep->produk()->updateOrCreate(
                [
                    'id_produk' => $jamurKrispi->id_produk
                ],
                [
                    'jumlah' => '1 bungkus'
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | 5. Nasi Gurih dengan Abon Jamur Tiram
        |--------------------------------------------------------------------------
        */

        $resep = Resep::updateOrCreate(
            [
                'judul' => 'Nasi Gurih dengan Abon Jamur Tiram'
            ],
            [
                'deskripsi' =>
                'Nasi gurih sederhana yang disajikan dengan Abon Jamur Tiram sebagai pelengkap.',

                'foto' => null,

                'bahan' =>
                "2 porsi nasi putih\n" .
                    "2 sdm Abon Jamur Tiram\n" .
                    "1 butir telur\n" .
                    "Mentimun secukupnya\n" .
                    "Bawang goreng secukupnya",

                'langkah_pembuatan' =>
                "1. Siapkan nasi putih hangat.\n" .
                    "2. Letakkan nasi di atas piring.\n" .
                    "3. Tambahkan Abon Jamur Tiram.\n" .
                    "4. Tambahkan telur sebagai pelengkap.\n" .
                    "5. Tambahkan mentimun dan bawang goreng.\n" .
                    "6. Sajikan selagi hangat.",

                'waktu_memasak' => 10,
            ]
        );


        if ($abon) {
            $resep->produk()->updateOrCreate(
                [
                    'id_produk' => $abon->id_produk
                ],
                [
                    'jumlah' => '2 sdm'
                ]
            );
        }
    }
}
