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
            'id' => '5100054985',
            'status' => 'done',
            'created_by' => 'Tên User 1 - 0000000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 1; $i <= 5; $i++) {
            DB::table('shipments')->insert([
                'id' => 'SHIPMENT' . $i,
                'status' => 'pending',
                'created_by' => 'Tên User '.$i.' - 000000000'.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
