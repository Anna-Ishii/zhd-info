<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualCategoryLevel1 extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name'
    ];

    public function level2s(): HasMany
    {
        return $this->hasMany(ManualCategoryLevel2::class, 'level1', 'id');
    }
}
