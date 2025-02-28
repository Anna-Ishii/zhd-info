<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRecipient extends Model
{
    protected $table = 'admin_recipients';

    protected $fillable = [
        'id',
        'email',
        'target',
        'created_at',
        'updated_at',
    ];
}
