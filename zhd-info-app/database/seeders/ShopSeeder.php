<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 店舗
        DB::table('shops')->insert(
            [
                [
                    'name' => '札幌発寒店',
                    'organization4_id' => '1',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '宇都宮平松本町',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '真岡店',
                    'organization4_id' => '2',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '八千代',
                    'organization4_id' => '3',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ]
            ]
        );
    }
}
