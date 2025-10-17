<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization1 extends Model
{
    protected $table = 'organization1';

    protected $fillable =
    [
        'id',
        'name',
    ];

    public function brand(): HasMany
    {
        return $this->hasMany(Brand::class, 'organization1_id', 'id');
    }

    public function shop(): HasMany
    {
        return $this->hasMany(Shop::class, 'id', 'organization1_id');
    }

    public function getOrganization5()
    {
        $organization5 = Shop::query()
            ->select(['organization5.id', 'organization5.name', 'organization5.order_no'])
            ->distinct('organization5.name')
            ->where('shops.organization1_id', '=', $this->id)
            ->whereNotNull('shops.organization5_id')
            ->leftjoin('organization5', 'shops.organization5_id', '=', 'organization5.id')
            ->orderby('organization5.order_no')
            ->get();

        return $organization5;
    }

    public function getOrganization4()
    {
        $organization4 = Shop::query()
            ->select(['organization4.id', 'organization4.name', 'organization4.order_no'])
            ->distinct('organization4.name')
            ->where('shops.organization1_id', '=', $this->id)
            ->whereNotNull('shops.organization4_id')
            ->leftjoin('organization4', 'shops.organization4_id', '=', 'organization4.id')
            ->orderby('organization4.order_no')
            ->get();

        return $organization4;
    }

    public function getOrganization3()
    {
        $organization3 = Shop::query()
            ->select(['organization3.id', 'organization3.name', 'organization3.order_no'])
            ->distinct('organization3.name')
            ->where('shops.organization1_id', '=', $this->id)
            ->whereNotNull('shops.organization3_id')
            ->leftjoin('organization3', 'shops.organization3_id', '=', 'organization3.id')
            ->orderby('organization3.order_no')
            ->get();

        return $organization3;
    }

    // ブロックがあるか
    public function isExistOrg5()
    {
        return Shop::query()
            ->where('organization1_id', $this->id)
            ->whereNotNull('organization5_id')
            ->exists();
    }

    // エリアがあるか
    public function isExistOrg4()
    {
        return Shop::query()
            ->where('organization1_id', $this->id)
            ->whereNotNull('organization4_id')
            ->exists();
    }

    // ディストリクトがあるか
    public function isExistOrg3()
    {
        return Shop::query()
            ->where('organization1_id', $this->id)
            ->whereNotNull('organization3_id')
            ->exists();
    }
}
