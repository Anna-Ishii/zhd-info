<?php

namespace App\Http\Repository;

use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class Organization1Repository
{
    // ブロックがあるか
    public static function isExistOrg5($organization1_id)
    {
        return Shop::select('organization5_id')
                ->whereNotNull('organization1_id', $organization1_id)
                ->exists();

    }

    // ブロックがあるか
    public static function isExistOrg4($organization1_id)
    {
        return Shop::select('organization4_id')
            ->whereNotNull('organization1_id', $organization1_id)
            ->exists();
    }

    public static function getOrg5($organization1_id)
    {
        return Shop::Join('organization5', 'shops.organization5_id', '=', 'organization5.id')
                ->distinct()
                ->select('shops.organization5_id as organization_id', 'organization5.name as organization_name')
                ->where('shops.organization1_id', '=', $organization1_id)->get();
    }

    public static function getOrg4($organization1_id)
    {
        return Shop::Join('organization4', 'shops.organization4_id', '=', 'organization4.id')
                ->distinct()
                ->select('shops.organization4_id as organization_id', 'organization4.name as organization_name')
                ->where('shops.organization1_id', '=', $organization1_id)->get();
    }
}
