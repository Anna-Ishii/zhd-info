<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ManualCategoryLevel2 extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'name',
        'level1'
    ];

    public function level1():BelongsTo
    {
        return $this->belongsTo(ManualCategoryLevel1::class, 'level1', 'id');
    }
}
