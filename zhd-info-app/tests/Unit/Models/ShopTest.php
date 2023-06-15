<?php

namespace Tests\Unit\Models;

use App\Models\Shop;
use Tests\TestCase;

class ShopTest extends TestCase
{
    public function test_Shopモデルからorganizationを取得できるか()
    {
        $shop_id = 1;
        $shop = Shop::find($shop_id);

        $this->assertEquals("北海道", $shop->organization4->name);
    }
}
