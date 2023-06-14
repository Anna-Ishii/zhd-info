<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Orgaization3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //　部署
        DB::table('organization3')->insert(
            [
                [
                    'name' => '東日本営業部',
                    'organization2_id' => '1'
                ],
                [
                    'name' => '中日本・関西営業部',
                    'organization2_id' => '1'
                ],
                [
                    'name' => '西日本営業部',
                    'organization2_id' => '1'
                ],
            ]
        );
    }
}
