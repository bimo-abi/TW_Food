<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Pengguna;

class TwfoodUserSeeder extends Seeder
{
    public function run(): void
    {
        Pengguna::updateOrCreate(
            [
                'email' => 'admin@twfood.com'
            ],
            [
                'nama' => 'Admin TWFood',
                'kata_sandi' => Hash::make('admin12345'),
                'nomor_telepon' => '083893395318',
                'peran' => 'mitra',
                'jenis_pelanggan' => null,
                'foto_profil' => null,
                'status_aktif' => true,
            ]
        );
    }
}
