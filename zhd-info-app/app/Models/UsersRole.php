<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersRole extends Model
{
    protected $table = 'users_roles';

    protected $fillable = [
        'id',
        'shop_id',
        'shop_code',
        'shop_name',
        'DM_id',
        'DM_name',
        'DM_email',
        'DM_view_notification',
        'BM_id',
        'BM_name',
        'BM_email',
        'BM_view_notification',
        'AM_id',
        'AM_name',
        'AM_email',
        'AM_view_notification',
        '4th_id',
        '4th_name',
        '4th_email',
        '4th_view_notification',
        '5th_id',
        '5th_name',
        '5th_email',
        'created_at',
        'updated_at',
    ];
}
