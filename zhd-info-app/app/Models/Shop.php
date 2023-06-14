<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    protected $table = 'shops';

    protected $fillable = 
        [
         'name', 
         'organization4_id'
        ];

    public function user(): HasOne
    {
        return $this->hasone(User::class, 'id', 'employee_code');
    }
}
