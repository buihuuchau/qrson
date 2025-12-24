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
        DB::table('code_products')->truncate();
        for ($i = 1; $i <= 5; $i++) {
            DB::table('code_products')->insert([
                'id' => 'CODEPRODUCTDOCUMENT1000000' . $i,
                'shipment_id' => 'SHIPMENT1',
                'document_id' => 'DOCUMENT1',
                'scan' => 'yes',
                'created_by' => 'Tên User 1 - 0000000001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 6; $i <= 9; $i++) {
            DB::table('code_products')->insert([
                'id' => 'CODEPRODUCTDOCUMENT1000000' . $i,
                'shipment_id' => 'SHIPMENT1',
                'document_id' => 'DOCUMENT1',
                'scan' => 'no',
                'created_by' => 'Tên User 1 - 0000000001',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('code_products')->insert([
            'id' => 'CODEPRODUCTDOCUMENT10000010',
            'shipment_id' => 'SHIPMENT1',
            'document_id' => 'DOCUMENT1',
            'scan' => 'no',
            'created_by' => 'Tên User 1 - 0000000001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 1; $i <= 5; $i++) {
            DB::table('code_products')->insert([
                'id' => 'CODEPRODUCTDOCUMENT3000000' . $i,
                'shipment_id' => 'SHIPMENT2',
                'document_id' => 'DOCUMENT3',
                'scan' => 'yes',
                'created_by' => 'Tên User 3 - 0000000003',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('code_product_temps')->truncate();
        for ($i = 1; $i <= 5; $i++) {
            DB::table('code_product_temps')->insert([
                'id' => 'CODEPRODUCTDOCUMENT2000000' . $i,
                'shipment_id' => 'SHIPMENT2',
                'document_id' => 'DOCUMENT2',
                'scan' => 'yes',
                'created_by' => 'Tên User 2 - 0000000002',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        for ($i = 6; $i <= 9; $i++) {
            DB::table('code_product_temps')->insert([
                'id' => 'CODEPRODUCTDOCUMENT2000000' . $i,
                'shipment_id' => 'SHIPMENT2',
                'document_id' => 'DOCUMENT2',
                'scan' => 'no',
                'created_by' => 'Tên User 2 - 0000000002',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
