<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization2 extends Model
{
    protected $table = 'organization2';

    protected $fillable = 
        ['name',
         'organization1_id'
    ];
}
