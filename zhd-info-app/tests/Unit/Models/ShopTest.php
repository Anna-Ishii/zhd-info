<?php

namespace Tests\Unit\Models;

use App\Models\Shop;
use Symfony\Component\VarDumper\Caster\RdKafkaCaster;
use Tests\TestCase;

class ShopTest extends TestCase
{
    public function test_Shopモデルからorganizationを取得できるか()
    {
        $shop_id = 1;
        $shop = Shop::find($shop_id);

        $this->assertEquals("北海道", $shop->organization4->name);
    }

    public function test_Shopモデルからユーザーの数を取得できるか()
    {
        $shop = Shop::withCount('user')->get();

        $this->assertEquals(2, $shop[0]->user_count);
    }

    public function test_Shopモデルから条件指定でユーザーの数を取得できるか()
    {
        $shop_id = 1;
        $shop = Shop::withCount('user')->whereIn('id', [1,2,3])->get();

        $this->assertEquals(2, $shop[0]->user_count);
    }
}
