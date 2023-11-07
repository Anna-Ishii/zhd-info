<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Crew extends Model
{
    protected $fillable =
    [
        'user_id',
        'name',
        'part_code',
        'birth_date',
        'register_date',
    ];
}
