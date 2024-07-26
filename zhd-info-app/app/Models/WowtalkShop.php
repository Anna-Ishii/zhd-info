<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WowtalkShop extends Model
{
    protected $table = 'wowtalk_shops';

    protected $fillable = [
        'id',
        'shop_code',
        'shop_name',
        'wowtalk_id',
        'notification_target',
        'created_at',
        'updated_at',
    ];
}
