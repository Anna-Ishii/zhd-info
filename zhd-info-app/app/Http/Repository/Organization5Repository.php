<?php

namespace App\Http\Repository;

use App\Models\Shop;

class Organization5Repository
{
    // ブロックを取得する
    public static function getOrg5($organization1_id)
    {
        $org5 = Shop::Join('organization5', 'shops.organization5_id', '=', 'organization5.id')
                    ->distinct()
                    ->select('shops.organization5_id', 'organization5.name')->where('shops.organization1_id', '=',$organization1_id)->get();
        return $org5;
    }
}