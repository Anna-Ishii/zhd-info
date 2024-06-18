<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualShop extends Model
{
    protected $table = 'manual_shop';

    protected $fillable = [
        'manual_id',
        'shop_id',
        'selected_flg',
        'created_at',
        'updated_at',
        'brand_id',
    ];
}
