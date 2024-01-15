<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    protected $table = 'brands';

    protected $fillable =
    [
        'id',
        'name',
        'organization1_id'
    ];
    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id', 'id');
    }
}
