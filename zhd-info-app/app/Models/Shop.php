<?php

namespace App\Models;

use App\Models\Traits\WhereLike;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use WhereLike;

    protected $table = 'shops';

    protected $fillable = 
        [
         'name',
         'shop_code',
         'brand_id',
         'organization5_id',
         'organization4_id',
         'organization3_id',
         'organization2_id',
         'organization1_id',
        ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'shop_id', 'id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    public function organization5(): BelongsTo
    {
        return $this->belongsTo(Organization5::class, 'organization5_id', 'id');
    }

    public function organization4(): BelongsTo
    {
        return $this->belongsTo(Organization4::class, 'organization4_id', 'id');
    }

    public function organization3(): BelongsTo
    {
        return $this->belongsTo(Organization3::class, 'organization3_id', 'id');
    }

    public function organization2(): BelongsTo
    {
        return $this->belongsTo(Organization2::class, 'organization2_id', 'id');
    }

    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id', 'id');
    }

    public function target_user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manual_user', 'shop_id', 'user_id')
                    ->withPivot('read_flg', 'user_id');
    }

    public function userCount()
    {
        return $this->user()->count();
    }

    // 現状は「業態省略名」+「店舗コード(4桁)」で登録していたので、
    // IMS連携に対応するため、初回のみ店舗コードをcsvの店舗コード(7桁 ~ )で更新する
    public static function update_shopcode($shop_code, $brand_id)
    {
        $code = substr($shop_code, -4);
        $shop = Shop::query()
            ->where("brand_id", $brand_id)
            ->whereRaw("substring(shop_code,-4) = ?", [$code])
            ->first();
        
        if(empty($shop)) return;
        $shop->shop_code = $shop_code;
        $shop->save();
    }
}
