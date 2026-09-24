<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\JamOperasionalOutlet;

class TwfoodOutletSeeder extends Seeder
{
    public function run(): void
    {
        // Membuat / memperbarui data outlet
        $outlet = Outlet::updateOrCreate(
            ['nama_outlet' => 'TWFood'],
            [
                'alamat' => 'Jl. Kertanegara IX Kel. No. 237, RT.07/RW.33, Jember Kidul, Kec. Kaliwates, Kabupaten Jember, Jawa Timur 68131',
                'nomor_telepon' => '+6283893395318',
                'latitude' => null,
                'longitude' => null,
                'deskripsi' => 'Outlet TWFood di Jember.',
                'foto' => null,
            ]
        );

        // Data jam operasional
        $jamOperasional = [
            [
                'hari' => 'senin',
                'jam_buka' => '08:30:00',
                'jam_tutup' => '17:00:00',
                'tutup' => false,
            ],
            [
                'hari' => 'selasa',
                'jam_buka' => '08:30:00',
                'jam_tutup' => '17:00:00',
                'tutup' => false,
            ],
            [
                'hari' => 'rabu',
                'jam_buka' => '08:30:00',
                'jam_tutup' => '19:00:00',
                'tutup' => false,
            ],
            [
                'hari' => 'kamis',
                'jam_buka' => '08:00:00',
                'jam_tutup' => '17:00:00',
                'tutup' => false,
            ],
            [
                'hari' => 'jumat',
                'jam_buka' => '08:00:00',
                'jam_tutup' => '17:30:00',
                'tutup' => false,
            ],
            [
                'hari' => 'sabtu',
                'jam_buka' => '08:00:00',
                'jam_tutup' => '12:00:00',
                'tutup' => false,
            ],
            [
                'hari' => 'minggu',
                'jam_buka' => null,
                'jam_tutup' => null,
                'tutup' => true,
            ],
        ];

        foreach ($jamOperasional as $jam) {
            JamOperasionalOutlet::updateOrCreate(
                [
                    'id_outlet' => $outlet->id_outlet,
                    'hari' => $jam['hari'],
                ],
                [
                    'jam_buka' => $jam['jam_buka'],
                    'jam_tutup' => $jam['jam_tutup'],
                    'tutup' => $jam['tutup'],
                ]
            );
        }
    }
}
