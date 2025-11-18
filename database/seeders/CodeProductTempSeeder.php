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
        DB::table('code_products')->truncate();
        DB::table('code_products')->insert([
            'id' => 'n92500000000000000000000000',
            'shipment_id' => '5100054985',
            'document_id' => '5002756032',
            'created_by' => 'Tên Admin - 0000000000',
            'scan' => 'yes',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('code_product_temps')->truncate();
        for ($i = 1; $i <= 5; $i++) {
            DB::table('code_product_temps')->insert([
                'id' => 'CODE_PRODUCT_' . $i,
                'shipment_id' => 'SHIPMENT1',
                'document_id' => 'DOCUMENT1',
                'user_id' => 3,
                'scan' => 'yes',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 6; $i <= 9; $i++) {
            DB::table('code_product_temps')->insert([
                'id' => 'CODE_PRODUCT_' . $i,
                'shipment_id' => 'SHIPMENT1',
                'document_id' => 'DOCUMENT1',
                'user_id' => 3,
                'scan' => 'no',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
