<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TwfoodProductSeeder::class,
            TwfoodKontenSeeder::class,
            TwfoodOutletSeeder::class,
            TwfoodUserSeeder::class,
            TwfoodResepSeeder::class,
        ]);
    }
}
