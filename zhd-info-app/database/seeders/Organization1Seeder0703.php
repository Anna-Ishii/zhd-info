<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Organization1Seeder0703 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 業態
        DB::table('organization1')->insert(
            [
                [
                    'name' => 'ON',
                ],
                [
                    'name' => 'HY',
                ],
                [
                    'name' => 'BB',
                ],
                [
                    'name' => 'TAG',
                ]
            ]
        );
    }
}
