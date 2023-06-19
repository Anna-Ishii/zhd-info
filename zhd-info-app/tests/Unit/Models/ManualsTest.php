<?php

namespace Tests\Unit\Models;

use App\Models\Manual;
use App\Models\Message;
use App\Models\Roll;
use Carbon\Carbon;
use Tests\TestCase;

class ManualsTest extends TestCase
{
    function test_マニュアルにコンテントを保存できるか()
    {
        // $manual = new Manual();
        // $manual->title = 'test';
        // $manual->description = 'マニュアル';
        // $manual->create_user_id = 1;
        // $manual->category_id = 1;
        // $manual->status = 0;
        // $manual->start_datetime = '2023-06-12 17:00';
        // $manual->end_datetime = '2023-06-12 17:00';
        // $manual->save();
        $content =
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
        ];
        $manual = Manual::find(1);
        $content = $manual->content()->createMany($content);
        $this->assertEquals("メニュー・マニュアル関連", $content->title);
    }
}