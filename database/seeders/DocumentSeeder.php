<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documents')->truncate();
        DB::table('documents')->insert([
            'id' => 'DOCUMENT1',
            'shipment_id' => 'SHIPMENT1',
            'total_current' => 10,
            'total' => 10,
            'status' => 'done',
            'created_by' => 'Tên User 1 - 0000000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('documents')->insert([
            'id' => 'DOCUMENT2',
            'shipment_id' => 'SHIPMENT2',
            'total_current' => 9,
            'total' => 10,
            'status' => 'pending',
            'created_by' => 'Tên User 2 - 0000000002',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('documents')->insert([
            'id' => 'DOCUMENT3',
            'shipment_id' => 'SHIPMENT2',
            'total_current' => 5,
            'total' => 5,
            'status' => 'done',
            'created_by' => 'Tên User 3 - 0000000003',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
