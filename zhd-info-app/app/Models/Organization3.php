<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization3 extends Model
{
    protected $table = 'organization3';

    protected $fillable =
    [
        'name',
        'organization2_id'
    ];

    public function organization2(): BelongsTo
    {
        return $this->belongsTo(Organization2::class, 'organization2_id', 'id');
    }

    public function organization4(): HasMany
    {
        return $this->hasMany(Organization4::class, 'organization3_id', 'id');
    }
}
