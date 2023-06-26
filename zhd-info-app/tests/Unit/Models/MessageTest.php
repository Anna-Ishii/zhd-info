<?php

namespace Tests\Unit\Models;

use App\Models\Message;
use App\Models\Roll;
use Carbon\Carbon;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function test_Messageモデルからロールモデルが取得できるか()
    {
        $message_id = 1;
        $message = Message::find($message_id);
        // $rolls = $message->roll()->pluck('rolls.id')->toArray();
        $this->assertEquals("クルー", $message->roll[0]->name);

    }
    /**
     * A basic unit test example.
     */
    public function test_insert()
    {
        $message = new Message();
        $message->title = "タイトル";
        $message->content_url = "https://jp-information-sys-html.dev.nssx.work/message/detail.html";
        $message->category_id = 1;
        $message->create_admin_id = 1;
        $message->emergency_flg = false;
        $message->start_datetime = Carbon::now()->format('Y-m-d H:i:s');
        $message->end_datetime = Carbon::now()->format('Y-m-d H:i:s');
        $result = $message->save();
        $this->assertEquals(true, $result);
    }

    public function test_Messageモデルからカテゴリモデルが取得できるか()
    {
        $message_id = 1;
        $message = Message::find($message_id);
        var_dump($message->category->name);
        $this->assertEquals("メニュー・マニュアル関連", $message->category->name);
    }

    public function test_メッセージモデルの作成時に該当のロールと該当の店舗情報を保存できるか()
    {
        //メッセージ作成
        $message = new Message();
        $message->title = "タイトル";
        $message->content_url = "https://jp-information-sys-html.dev.nssx.work/message/detail.html";
        $message->category_id = 1;
        $message->create_admin_id = 1;
        $message->emergency_flg = false;
        $message->start_datetime = Carbon::now()->format('Y-m-d H:i:s');
        $message->end_datetime = Carbon::now()->format('Y-m-d H:i:s');
        $message->save();
        $message->roll()->attach([1,2]);
        $message->organization4()->attach([1,2]);
        $roll = $message->roll[0]->id;
        
        $this->assertEquals(1, $roll);


    }

}
