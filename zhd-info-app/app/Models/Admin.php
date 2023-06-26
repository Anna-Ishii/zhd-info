<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Model
{
    use SoftDeletes;

    protected $table = 'admin';
    protected $fillable =
    [
        'name',
        'email',
        'password',
        'employee_code',
    ];

}
