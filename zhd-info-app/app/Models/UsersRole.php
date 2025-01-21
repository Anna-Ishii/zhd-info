<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersRole extends Model
{
    protected $table = 'users_roles';

    protected $fillable = [
        'id',
        'user_id',
        'shop_id',
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
        'created_at',
        'updated_at',
    ];
}
