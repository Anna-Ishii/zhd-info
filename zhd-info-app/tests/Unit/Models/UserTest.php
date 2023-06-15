<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_Userモデルからメッセージが取得できるか()
    {
        $user_id = 1;
        $user = User::find($user_id);
        $msg_id = $user->message[0]->id;
        $this->assertEquals(1, $msg_id);
    }

    public function test_Userをロールと店舗でwhereする()
    {
        $roll_id = [1, 2];
        $shop_id = 1;
        $user = User::select([
            'id',
            'shop_id'
        ])
        ->from('user')
        ->join('shops.id', '=', 'user.shop_id')
        ->whereIn('roll_id', $roll_id)
        ->where('user.shop_id', '=', 'user.id')
        ->get();

        $this->assertEquals(1, $user[0]->id);
    }
}