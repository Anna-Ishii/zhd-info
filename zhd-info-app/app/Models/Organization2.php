<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization2 extends Model
{
    protected $table = 'organization2';

    protected $fillable = 
    [
         'name',
         'organization1_id'
    ];

    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id', 'id');
    }
    
    public function organization3(): HasMany
    {
        return $this->hasMany(Organization3::class, 'organization2_id', 'id');
    }
}
