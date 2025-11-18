<?php

namespace Database\Seeders;

use App\Models\CodeProductTemp;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ShipmentSeeder::class,
            DocumentSeeder::class,
            CodeProductTempSeeder::class,
        ]);
    }
}
