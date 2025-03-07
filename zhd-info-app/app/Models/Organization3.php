<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization3 extends Model
{
    protected $table = 'organization3';

    protected $fillable =
    [
        'id',
        'name',
        'display_name',
        'organization1_id'
    ];
}
