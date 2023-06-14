<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Message_UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('message_user')->insert(
            [
                [
                    'employee_code' => '1234567890',
                    'message_id' => '1',
                    'read_flg' => false,
                    'shop_id' => 1,
                ]
            ]
        );
    }
}
