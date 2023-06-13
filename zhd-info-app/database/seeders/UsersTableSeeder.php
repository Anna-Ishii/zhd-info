<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert(
        [
            [
            'name' => 'test',
            'email' => 'test@email.co.jp',
            'password' => 'password',
            'employee_code' => '1234567890',
            'shop_code' => 0,
            'roll_id' => 0,
            ]
        ]
        );
    }
}
