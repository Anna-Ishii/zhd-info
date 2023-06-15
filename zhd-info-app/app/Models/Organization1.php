<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization1 extends Model {
    protected $table = 'organization1';

    protected $fillable = 
    [
        'name'
    ];

    public function organization2(): HasMany
    {
        return $this->hasMany(Organization2::class,'organization1_id', 'id');
    }
}