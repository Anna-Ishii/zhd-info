<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualTagMaster extends Model
{
    protected $table = 'manual_tag_master';

    protected $fillable = [
        'id',
        'name'
    ];

}
