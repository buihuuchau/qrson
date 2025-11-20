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
            'id' => '5002756032',
            'shipment_id' => '5100054985',
            'total_current' => 1,
            'total' => 1,
            'status' => 'done',
            'created_by' => 'Nguyen Van A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('documents')->insert([
            'id' => 'DOCUMENT1',
            'shipment_id' => 'SHIPMENT1',
            'total_current' => 9,
            'total' => 10,
            'status' => 'pending',
            'created_by' => 'Nguyen Van B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 2; $i <= 5; $i++) {
            DB::table('documents')->insert([
                'id' => 'DOCUMENT' . $i,
                'shipment_id' => 'SHIPMENT1',
                'total_current' => 0,
                'total' => $i * 10,
                'status' => 'pending',
                'created_by' => 'Nguyen Van ' . $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
