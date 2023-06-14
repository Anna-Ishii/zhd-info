<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization3 extends Model
{
    protected $table = 'organization3';

    protected $fillable =
    [
        'name',
        'organization2_id'
    ];
}
