<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shipments')->truncate();
        DB::table('shipments')->insert([
            'id' => 'SHIPMENT1',
            'status' => 'done',
            'created_by' => 'Tên User 1 - 0000000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('shipments')->insert([
            'id' => 'SHIPMENT2',
            'status' => 'pending',
            'created_by' => 'Tên User 2 - 0000000002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
