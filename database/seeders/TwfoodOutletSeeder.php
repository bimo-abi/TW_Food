<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;

class TwfoodOutletSeeder extends Seeder
{
    public function run(): void
    {
        Outlet::updateOrCreate(
            [
                'nama_outlet' => 'TWFood'
            ],
            [
                'alamat' => 'Jl. Kertanegara IX/237, Kaliwates, Jember - Jawa Timur',
                'nomor_telepon' => '+6283893395318',
                'latitude' => null,
                'longitude' => null,
                'jam_buka' => '09:00:00',
                'jam_tutup' => '21:00:00',
                'deskripsi' => 'Outlet TWFood di Jember.',
                'foto' => null,
            ]
        );
    }
}
