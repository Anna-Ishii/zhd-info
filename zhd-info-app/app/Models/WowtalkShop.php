<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WowtalkShop extends Model
{
    protected $table = 'wowtalk_shops';

    protected $fillable = [
        'id',
        'shop_id',
        'shop_code',
        'shop_name',
        'wowtalk1_id',
        'notification_target1',
        'business_notification1',
        'wowtalk2_id',
        'notification_target2',
        'business_notification2',
        'created_at',
        'updated_at',
    ];
}
