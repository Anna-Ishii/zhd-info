<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Orgaization4Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //ブロック
        DB::table('organization4')->insert(
            [
                [
                    'name' => '北海道',
                    'organization3_id' => '0'
                ],
                [
                    'name' => '東北・北関東',
                    'organization3_id' => '0'
                ],
                [
                    'name' => '東京・川崎',
                    'organization3_id' => '3'
                ]
            ]
        );
    }
}
