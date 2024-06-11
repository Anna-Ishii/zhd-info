<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageShop extends Model
{
    protected $table = 'message_shop';

    protected $fillable = [
        'message_id',
        'shop_id',
        'selected_flg',
        'created_at',
        'updated_at',
    ];
}
