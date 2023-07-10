<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization1 extends Model 
{
    protected $table = 'organization1';

    protected $fillable = 
    [
        'id',
        'name',
    ];

    public function brand(): HasMany
    {
        return $this->hasMany( Brand::class, 'organization1_id', 'id');
    }

}