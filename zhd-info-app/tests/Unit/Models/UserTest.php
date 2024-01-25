<?php

namespace Tests\Unit\Models;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Message;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Models\Roll;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function factory(): Void
    {
        Organization1::create([
            'name' => "test業態"
        ]);
        Organization2::create([
            'name' => "test部署"
        ]);
        Organization3::create([
            'name' => "testディストリクト"
        ]);
        Organization4::create([
            'name' => "testエリア"
        ]);
        Organization5::create([
            'name' => "testブロック"
        ]);
        Brand::create([
            'name' => "testブランド",
            'organization1_id' => 1,
            'brand_code' => "testコード"
        ]);
        Admin::create([
            'name' => '管理者',
            'password' => 'password',
            'employee_code' => 'test01',
            'organization1_id' => 1,
        ]);
        Message::create([
            'title' => 'testtitle',
            'create_admin_id' => 1,
            'number' => 1,
        ]);
        Message::create([
            'title' => 'testtitle2',
            'create_admin_id' => 1,
            'number' => 2,
        ]);
        Message::create([
            'title' => 'testtitle3',
            'create_admin_id' => 1,
            'number' => 3,
        ]);
        Shop::create([
            'name' => 'testショップ',
            'shop_code' => "test0001",
            'brand_id' => 1,
            'organization1_id' => 1,
            'organization5_id' => 1
        ]);

        DB::insert('insert into message_organization (message_id, organization1_id, organization5_id) values (1, 1, 1)');
        DB::insert('insert into message_organization (message_id, organization1_id, organization4_id) values (1, 1, 1)');
        DB::insert('insert into message_organization (message_id, organization1_id, organization4_id) values (2, 1, 1)');
        DB::insert('insert into message_organization (message_id, organization1_id, organization4_id) values (3, 1, 1)');
        DB::insert('insert into message_brand (message_id, brand_id ) values (1, 1)');
        DB::insert('insert into message_brand (message_id, brand_id ) values (2, 1)');
        DB::insert('insert into message_brand (message_id, brand_id ) values (3, 1)');
        Roll::create([
            'name' => "一般"
        ]);
    }

    // public function test_Userモデルからメッセージが取得できるか()
    // {
    //     $user_id = 1;
    //     $user = User::find($user_id);
    //     $msg_id = $user->message[0]->id;
    //     $this->assertEquals(1, $msg_id);
    // }

    // public function test_Userをロールと店舗でwhereする()
    // {
    //     $roll_id = [1, 2];
    //     $shop_id = 1;
    //     $user = User::select([
    //         'id',
    //         'shop_id'
    //     ])
    //     ->from('user')
    //     ->join('shops.id', '=', 'user.shop_id')
    //     ->whereIn('roll_id', $roll_id)
    //     ->where('user.shop_id', '=', 'user.id')
    //     ->get();

    //     $this->assertEquals(1, $user[0]->id);
    // }

    
    public function test_distributeMessages()
    {
        $this->factory();

        $user = User::create([
            'name' => 'test',
            'belong_label' => "test_label",
            'email' => "test@email.com",
            'password' => "password",
            'employee_code' => "test_code",
            'shop_id' => 1,
            'roll_id' => 1
        ]);

        DB::insert('insert into message_user (user_id, message_id, shop_id) value (1, 2, 1)');
        DB::insert('insert into message_user (user_id, message_id, shop_id) value (1, 3, 1)');

        $count =  $user->message()->count();
        $this->assertEquals(2, $count);

        $user->distributeMessages();
        $count =  $user->message()->count();

        $this->assertEquals(1, $count);
        // DB::insert('insert int'''''o message_user (user_id, message_id, shop_id) values (1, 1, 1)');

    }

}