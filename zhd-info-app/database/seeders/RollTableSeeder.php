<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RollTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rolls')->insert(
            [
                [
                    'name' => '一般'
                ],
                [
                    'name' => 'クルー'
                ],
                [
                    'name' => '時間管理者'
                ],
                [
                    'name' => '店長'
                ]
            ]
        );
    }
}
