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
                    'organization3_id' => '1'
                ],
                [
                    'name' => '東北・北関東',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '千葉',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '東京・埼玉',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '西東京',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '横浜',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '神奈川・山梨',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '沖縄',
                    'organization3_id' => '1'
                ],
                [
                    'name' => '静岡',
                    'organization3_id' => '2'
                ],
                [
                    'name' => '東海',
                    'organization3_id' => '2'
                ],
                [
                    'name' => '中部・北陸',
                    'organization3_id' => '2'
                ],
                ///⇧一旦ここまで
            ]
        );
    }
}
