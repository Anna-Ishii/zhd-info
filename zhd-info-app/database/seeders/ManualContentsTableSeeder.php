<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManualContentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('manualcontents')->insert(
            [
                [
                    'manual_id' => 1,
                    'title' => 'test1',
                    'content_url' => 'https://jp-information-sys-html.dev.nssx.work/message/detail.html',
                    'title' => '手順1',
                    'description' => 'マニュアル説明1',
                    'order_no' => 1,
                ],
                [
                    'manual_id' => 1,
                    'title' => 'test2',
                    'content_url' => 'https://jp-information-sys-html.dev.nssx.work/message/detail.html',
                    'title' => '手順2',
                    'description' => 'マニュアル説明2',
                    'order_no' => 2,
                ],
            ]
        );
    }
}
