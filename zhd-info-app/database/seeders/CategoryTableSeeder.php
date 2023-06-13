<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert(
            [
                [
                    'name' => 'メニュー・マニュアル関連'
                ],
                [
                    'name' => '人事・総務'
                ],
                [
                    'name' => '情報共有'
                ],
                [
                    'name' => 'イレギュラー'
                ],
            ]
        );
    }
}
