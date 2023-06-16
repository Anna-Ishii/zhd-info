<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualCategoiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('manualcategories')->insert(
            [
                [
                    'name' => '商品マニュアル'
                ],
                [
                    'name' => 'オペレーションマニュアル'
                ],
                [
                    'name' => '教育動画'
                ],
                [
                    'name' => 'トピックス'
                ],
                [
                    'name' => 'Channel'
                ],
            ]
        );
    }
}
