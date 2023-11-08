<?php

namespace Tests\Unit\Models;

use App\Models\Brand;
use App\Models\Organization1;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\VarDumper\Caster\RdKafkaCaster;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp():void
    {
        parent::setUp();

        Organization1::create([
            'name' => "test業態"
        ]);
        Brand::create([
            'name' => "testブランド",
            'organization1_id' => 1,
            'brand_code' => "testコード"
        ]);
        Shop::create([
            'name' => "shop",
            'shop_code' => "test0001",
            'organization1_id' => 1,
            'brand_id' => 1,
        ]);
    }

    public function test_入力shopcodeの下四桁とブランドIDが一致する店舗は新しいshopcodeに更新()
    {
        // 入力の店舗コードは1110001とする
        Shop::update_shopcode('1110001', 1);
        $after_shop = Shop::find(1);
        $this->assertEquals('1110001', $after_shop->shop_code);
    }
}
