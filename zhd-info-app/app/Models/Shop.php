<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shop extends Model
{
    protected $table = 'shops';

    protected $fillable = 
        [
         'name',
         'shop_code',
         'organization4_id',
         'organization3_id',
         'organization2_id',
         'organization1_id',
        ];

    public function user(): HasOne
    {
        return $this->hasone(User::class, 'id', 'user_id');
    }

    public function organization4(): BelongsTo
    {
        return $this->belongsTo(Organization4::class, 'organization4_id', 'id');
    }

    public function organization3(): BelongsTo
    {
        return $this->belongsTo(Organization3::class, 'organization3_id', 'id');
    }

    public function organization2(): BelongsTo
    {
        return $this->belongsTo(Organization2::class, 'organization2_id', 'id');
    }

    public function organization1(): BelongsTo
    {
        return $this->belongsTo(Organization1::class, 'organization1_id', 'id');
    }
}
