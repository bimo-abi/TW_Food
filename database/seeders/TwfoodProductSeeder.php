<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produk;
use App\Models\VarianProduk;
use App\Models\DaftarHarga;

class TwfoodProductSeeder extends Seeder
{
    public function run(): void
    {
        // =========================================================
        // 1. KALDU JAMUR TIRAM DAN SHITAKE
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Kaldu Jamur Tiram dan Shitake'
            ],
            [
                'deskripsi' => 'Kaldu Jamur Tewil terbuat dari ekstrak jamur tiram dan jamur shitake yang dipadukan dengan bumbu lainnya seperti seledri, soya, bawang bombay, bawang merah, bawang putih, merica, dan garam.',
                'foto_produk' => 'products/kaldu-jamur-tiram-shitake.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 18000,
                'minimal_pembelian' => 2,
                'satuan_minimal' => 'doz',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 20000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 2. KALDU JAMUR TIRAM MPASI
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Kaldu Jamur Tiram MPASI'
            ],
            [
                'deskripsi' => 'Kaldu Jamur Tewil terbuat dari ekstrak jamur tiram dan udang dengan kandungan protein lebih tinggi, cocok untuk MPASI.',
                'foto_produk' => null,
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 18000,
                'minimal_pembelian' => 2,
                'satuan_minimal' => 'doz',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 20000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 3. JAMUR TIRAM KRISPI
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Jamur Tiram Krispi'
            ],
            [
                'deskripsi' => 'Jamur Tiram Crispy dari Tewil yang gurih dan siap menemani hari bersama keluarga. Digoreng dengan sedikit minyak dan tanpa residu dengan pengolahan higienis.',
                'foto_produk' => 'products/jamur-tiram-krispi.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 15000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 20000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 4. KECAP JAMUR TIRAM
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Kecap Jamur Tiram'
            ],
            [
                'deskripsi' => 'Kecap yang terbuat dari fermentasi jamur tiram dengan rempah asli Indonesia, gula merah dan wijen.',
                'foto_produk' => 'products/kecap-jamur-tiram.png',
                'status_aktif' => true,
            ]
        );

        // -------------------------
        // Varian M
        // -------------------------

        $varianM = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'M',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianM->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 10000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianM->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 12000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        // -------------------------
        // Varian P
        // -------------------------

        $varianP = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'P',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianP->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 12000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianP->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 15000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        // -------------------------
        // Varian A
        // -------------------------

        $varianA = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'A',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianA->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 8000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varianA->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 10000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 5. MIE INSTAN JAMUR TIRAM
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Mie Instan Jamur Tiram'
            ],
            [
                'deskripsi' => 'Mie Jamur Tiram Kelor Tewil adalah mie yang dibuat dari tepung tapioka, tepung jamur, dan bubur daun kelor segar. Diperkaya dengan telur sebagai penambah protein hewani dan dilengkapi dengan bumbu pelengkap.',
                'foto_produk' => 'products/mie-instan-jamur-tiram.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 4000,
                'minimal_pembelian' => 24,
                'satuan_minimal' => 'doz',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 5000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 6. STIK JANTUNG PISANG
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Stik Jantung Pisang'
            ],
            [
                'deskripsi' => 'Camilan enak yang terbuat dari jantung pisang, bagian tanaman yang bergizi karena kaya vitamin dan mineral seperti kalium, vitamin A, vitamin C, vitamin E, asam lemak, dan asam amino.',
                'foto_produk' => 'products/stik-jantung-pisang.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 15000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 20000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 7. ABON JAMUR TIRAM
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Abon Jamur Tiram'
            ],
            [
                'deskripsi' => 'Abon vegan-friendly dari jamur tiram sebagai pengganti abon daging. Tekstur jamur tiram yang berserat membuat tekstur abon jamur tiram mirip abon daging. Penambahan jamur shitake menjadikan rasanya lebih lezat.',
                'foto_produk' => 'products/abon-jamur-tiram.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 20000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 25000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'pc',
                'status_aktif' => true,
            ]
        );


        // =========================================================
        // 8. KERUPUK JAMUR TIRAM
        // =========================================================

        $produk = Produk::updateOrCreate(
            [
                'nama_produk' => 'Kerupuk Jamur Tiram'
            ],
            [
                'deskripsi' => 'Kerupuk sehat yang terbuat dari jamur tiram, tepung tapioka dan rempah. Terdiri dari Jamur Original, Jamur & Wortel dan Jamur & Daun Kelor.',
                'foto_produk' => 'products/kerupuk-jamur-tiram.png',
                'status_aktif' => true,
            ]
        );

        $varian = VarianProduk::updateOrCreate(
            [
                'id_produk' => $produk->id_produk,
                'nama_varian' => 'Reguler',
            ],
            [
                'satuan_jual' => 'pc',
                'status_aktif' => true,
            ]
        );

        // Harga Grosir
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'grosir',
            ],
            [
                'harga' => 35000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => 'kg',
                'status_aktif' => true,
            ]
        );

        // Harga Ecer
        DaftarHarga::updateOrCreate(
            [
                'id_varian' => $varian->id_varian,
                'jenis_harga' => 'ecer',
            ],
            [
                'harga' => 12000,
                'minimal_pembelian' => 1,
                'satuan_minimal' => '185gr',
                'status_aktif' => true,
            ]
        );
    }
}
