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
            'name' => 'JP担当者',
            'belong_label' => 'ジョリーパスタ',
            'shop_id' => 1,
            'employee_code' => '1234567890',
            'password' => 'password',
            'email' => 'test@email.co.jp',
            'roll_id' => 1,
            ]
        ]
        );
    }
}
