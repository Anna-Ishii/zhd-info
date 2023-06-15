<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('messages')->insert(
            [
                [
                    'title' => 'test',
                    'content_url' => 'https://jp-information-sys-html.dev.nssx.work/message/detail.html',
                    'create_user_id' => 1,
                    'category_id' => 1,
                    'status' => 0,
                    'emergency_flg' => false,
                    'start_datetime' => '2023-06-12 17:00',
                    'end_datetime' => '2023-06-13 17:00'
                ]
            ]
        );
    }
}
