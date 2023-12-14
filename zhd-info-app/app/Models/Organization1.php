<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        return $this->hasMany(Brand::class, 'organization1_id', 'id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'id', 'organization1_id');
    }

    public function getOrganization5(): array
    {
        $organization5 = Shop::query()
            ->select('organization5.name')
            ->distinct('organization5.name')
            ->where('organization1_id', '=', $this->id)
            ->leftjoin('organization5', 'organization5_id', '=', 'organization5.id')
            ->get()
            ->toArray();

        return $organization5;
    }
}
