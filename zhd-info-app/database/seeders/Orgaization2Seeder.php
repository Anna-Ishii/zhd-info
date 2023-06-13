<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Orgaization2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ブランド
        DB::table('organization2')->insert(
            [
                [
                    'name' => 'ジョリーパスタ',
                    'organization1_id' => '0'
                ],
                [
                    'name' => 'ジョリーオックス',
                    'organization1_id' => '0'
                ],
                [
                    'name' => 'オリーブの丘',
                    'organization1_id' => '1'
                ]
            ]
        );
    }
}
