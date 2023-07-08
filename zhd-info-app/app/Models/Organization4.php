<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization4 extends Model
{
    protected $table = 'organization4';

    protected $fillable =
    [
        'id',
        'name',
    ];

    public function shop(): HasMany
    {
        return $this->hasMany(Shop::class, 'organization4_id', 'id');
    }
}
