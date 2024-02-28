<?php

namespace App\Http\Repository;

use App\Models\Shop;
use Illuminate\Support\Facades\DB;

class Organization1Repository
{
    // ブロックがあるか
    public static function isExistOrg5($organization1_id)
    {
        return Shop::query()
                ->where('organization1_id', $organization1_id)
                ->whereNotNull('organization5_id')
                ->exists();
    }

    // エリアがあるか
    public static function isExistOrg4($organization1_id)
    {
        return Shop::query()
                ->where('organization1_id', $organization1_id)
                ->whereNotNull('organization4_id')
                ->exists();
    }

    // ディストリクトがあるか
    public static function isExistOrg3($organization1_id)
    {
        return Shop::query()
            ->where('organization1_id', $organization1_id)
            ->whereNotNull('organization3_id')
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

    public static function getOrg3($organization1_id)
    {
        return Shop::Join('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->distinct()
            ->select('shops.organization3_id as organization_id', 'organization3.name as organization_name')
            ->where('shops.organization1_id', '=', $organization1_id)->get();
    }

    public static function getOrg2($organization1_id)
    {
        return Shop::Join('organization2', 'shops.organization2_id', '=', 'organization2.id')
            ->distinct()
            ->select('shops.organization2_id as organization_id', 'organization2.name as organization_name')
            ->where('shops.organization1_id', '=', $organization1_id)->get();
    }
}
