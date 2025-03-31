<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization5 extends Model
{
    protected $table = 'organization5';

    protected $fillable =
    [
        'id',
        'name',
        'display_name',
        'organization1_id'
    ];

}
