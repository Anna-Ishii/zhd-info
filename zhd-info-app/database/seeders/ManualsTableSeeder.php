<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('manuals')->insert(
            [
                [
                    'title' => 'test',
                    'content_url' => 'https://jp-information-sys-html.dev.nssx.work/message/detail.html',
                    'description' => 'マニュアル説明',
                    'create_admin_id' => 1,
                    'category_id' => 1,
                    'start_datetime' => '2023-06-12 17:00',
                    'end_datetime' => '2023-06-13 17:00'
                ]
            ]
        );
    }
}
