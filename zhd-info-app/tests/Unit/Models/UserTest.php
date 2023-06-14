<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_Userモデルからメッセージが取得できるか()
    {
        $user_employee_code = 1234567890;
        $user = User::find($user_employee_code);
        $this->assertEquals(true, isset($user->message->title));
    }
}