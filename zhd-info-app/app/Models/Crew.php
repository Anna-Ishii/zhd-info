<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Crew extends Model
{
    protected $fillable =
    [
        'user_id',
        'name',
        'name_kana',
        'part_code',
        'my_number',
        'birth_date',
        'register_date',
    ];
}
