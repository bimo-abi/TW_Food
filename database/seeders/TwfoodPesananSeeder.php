<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengguna;
use App\Models\Produk;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Alamat;
use App\Models\Outlet;

class TwfoodPesananSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DATA PELANGGAN
        |--------------------------------------------------------------------------
        */

        $pelanggan = Pengguna::updateOrCreate(
            [
                'email' => 'pelanggan@twfood.com'
            ],
            [
                'nama' => 'Budi Santoso',
                'kata_sandi' => bcrypt('pelanggan123'),
                'nomor_telepon' => '081234567890',
                'peran' => 'pelanggan',
                'jenis_pelanggan' => 'umum',
                'status_aktif' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | ALAMAT PELANGGAN
        |--------------------------------------------------------------------------
        */

        $alamat = Alamat::updateOrCreate(
            [
                'id_pengguna' => $pelanggan->id_pengguna,
                'label' => 'Rumah'
            ],
            [
                'nama_penerima' => 'Budi Santoso',
                'nomor_telepon' => '081234567890',
                'alamat_lengkap' => 'Jl. Contoh No. 10',
                'kota' => 'Jember',
                'provinsi' => 'Jawa Timur',
                'kode_pos' => '68121',
                'alamat_utama' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | OUTLET
        |--------------------------------------------------------------------------
        */

        $outlet = Outlet::first();

        /*
        |--------------------------------------------------------------------------
        | PRODUK
        |--------------------------------------------------------------------------
        */

        $varian = Produk::with('varian')
            ->where('status_aktif', true)
            ->first()
            ?->varian
            ->first();

        if (!$varian) {
            $this->command->error(
                'Data varian produk belum tersedia.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */

        $harga = $varian->daftarHarga()
            ->where('jenis_harga', 'ecer')
            ->where('status_aktif', true)
            ->first();

        if (!$harga) {
            $this->command->error(
                'Harga ecer produk belum tersedia.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | PESANAN DELIVERY
        |--------------------------------------------------------------------------
        */

        $jumlahDelivery = 2;

        $subtotalDelivery =
            $harga->harga * $jumlahDelivery;

        $pesananDelivery = Pesanan::updateOrCreate(
            [
                'nomor_pesanan' => 'TEST-DELIVERY-001'
            ],
            [
                'id_pengguna' => $pelanggan->id_pengguna,
                'id_alamat' => $alamat->id_alamat,
                'id_outlet' => $outlet?->id_outlet,
                'saluran_pesanan' => 'online',
                'jenis_pesanan' => 'delivery',
                'subtotal_produk' => $subtotalDelivery,
                'diskon' => 0,
                'total_pesanan' => $subtotalDelivery,
                'status_pembayaran' => 'lunas',
                'status_pesanan' => 'pesanan_diterima',
                'kurir' => null,
                'nomor_resi' => null,
                'tautan_pelacakan' => null,
                'catatan' => 'Pesanan dummy untuk testing delivery.',
            ]
        );

        DetailPesanan::updateOrCreate(
            [
                'id_pesanan' => $pesananDelivery->id_pesanan,
                'id_varian' => $varian->id_varian,
            ],
            [
                'nama_produk_saat_pesan' =>
                $varian->produk->nama_produk,

                'nama_varian_saat_pesan' =>
                $varian->nama_varian,

                'harga_saat_pesan' =>
                $harga->harga,

                'jumlah' =>
                $jumlahDelivery,

                'subtotal' =>
                $subtotalDelivery,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | PESANAN PICKUP
        |--------------------------------------------------------------------------
        */

        $jumlahPickup = 1;

        $subtotalPickup =
            $harga->harga * $jumlahPickup;

        $pesananPickup = Pesanan::updateOrCreate(
            [
                'nomor_pesanan' => 'TEST-PICKUP-001'
            ],
            [
                'id_pengguna' => $pelanggan->id_pengguna,
                'id_alamat' => null,
                'id_outlet' => $outlet?->id_outlet,
                'saluran_pesanan' => 'online',
                'jenis_pesanan' => 'pickup',
                'subtotal_produk' => $subtotalPickup,
                'diskon' => 0,
                'total_pesanan' => $subtotalPickup,
                'status_pembayaran' => 'lunas',
                'status_pesanan' => 'pesanan_diterima',
                'kurir' => null,
                'nomor_resi' => null,
                'tautan_pelacakan' => null,
                'catatan' => 'Pesanan dummy untuk testing pickup.',
            ]
        );

        DetailPesanan::updateOrCreate(
            [
                'id_pesanan' => $pesananPickup->id_pesanan,
                'id_varian' => $varian->id_varian,
            ],
            [
                'nama_produk_saat_pesan' =>
                $varian->produk->nama_produk,

                'nama_varian_saat_pesan' =>
                $varian->nama_varian,

                'harga_saat_pesan' =>
                $harga->harga,

                'jumlah' =>
                $jumlahPickup,

                'subtotal' =>
                $subtotalPickup,
            ]
        );

        $this->command->info(
            'Dummy pesanan delivery dan pickup berhasil dibuat.'
        );
    }
}
