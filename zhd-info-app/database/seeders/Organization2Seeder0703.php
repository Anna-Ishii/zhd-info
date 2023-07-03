<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Organization2Seeder0703 extends Seeder
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
                    'name' => 'ジョリーオックス',
                    'organization1_id' => '1'
                ]
            ]
        );
    }
}
