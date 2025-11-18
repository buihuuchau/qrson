<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->truncate();
        DB::table('users')->insert([
            'phone' => '0000000000',
            'password' => bcrypt('adminpassword'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        for ($i = 1; $i <= 5; $i++) {
            DB::table('users')->insert([
                'phone' => '000000000' . $i,
                'password' => bcrypt('userpassword'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
