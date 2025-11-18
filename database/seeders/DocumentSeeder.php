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
            'total' => 3,
        ]);
        for ($i = 1; $i <= 2; $i++) {
            DB::table('documents')->insert([
                'id' => 'DOCUMENT' . $i,
                'shipment_id' => '5100054985',
            ]);
        }
    }
}
