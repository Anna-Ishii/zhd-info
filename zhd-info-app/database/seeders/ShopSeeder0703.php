<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder0703 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('shops')->insert(
            [
                [
                    'name' => '徳山店',
                    'organization4_id' => '19',
                    'organization3_id' => '3',
                    'organization2_id' => '2',
                    'organization1_id' => '1'
                ],
                [
                    'name' => '南越谷店',
                    'organization4_id' => '4',
                    'organization3_id' => '1',
                    'organization2_id' => '1',
                    'organization1_id' => '1'
                ]
            ]
        );
    }
}