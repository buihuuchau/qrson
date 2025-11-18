<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeProductTempSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('code_product_temps')->truncate();
        DB::table('code_product_temps')->insert([
            'id' => 'n92500000000000000000000000',
            'shipment_id' => '5100054985',
            'document_id' => '5002756032',
            'user_id' => 2,
            'scan' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 1; $i <= 2; $i++) {
            DB::table('code_product_temps')->insert([
                'id' => 'n9250000000000000000000000' . $i,
                'shipment_id' => '5100054985',
                'document_id' => '5002756032',
                'user_id' => 2,
                'scan' => 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
