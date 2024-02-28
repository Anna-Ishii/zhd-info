<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Organization1;
use App\Models\Organization2;
use App\Models\Organization3;
use App\Models\Organization4;
use App\Models\Organization5;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp():void
    {
        parent::setUp();
    }

    public function factory():Void
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
        


    }

    // public function test_入力shopcodeの下四桁とブランドIDが一致する店舗は新しいshopcodeに更新()
    // {
    //     $this->factory();

    //     Shop::create([
    //         'name' => "shop",
    //         'shop_code' => "test0001",
    //         'organization1_id' => 1,
    //         'brand_id' => 1,
    //     ]);
    //     // 入力の店舗コードは1110001とする
    //     Shop::update_shopcode('1110001', 1);
    //     $after_shop = Shop::find(1);
    //     $this->assertEquals('1110001', $after_shop->shop_code);
    // }

    public function test_updateOrCreateで店舗が作成されたら検知できるか()
    {
        $this->factory();
        Shop::create([
            'name' => 'testショップ',
            'shop_code' => "test0001",
            'brand_id' => 1,
            'organization1_id' => 1,
        ]);

        $shop = Shop::updateOrCreate(
            [
                'shop_code' => "test0001",
                'brand_id' => 1
            ],
            [
                'name' => "shop",
                'organization1_id' => 1,
                'brand_id' => 1
            ]
        );
        $this->assertTrue($shop->wasChanged());
    }
}
