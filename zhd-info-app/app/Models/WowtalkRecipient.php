<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WowtalkRecipient extends Model
{
    protected $table = 'wowtalk_recipients';

    protected $fillable = [
        'id',
        'email',
        'target',
        'created_at',
        'updated_at',
    ];
}
