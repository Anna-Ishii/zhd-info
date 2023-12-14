<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualCategoryLevel1 extends Model
{
    use HasFactory;

    public function level2s():BelongsTo
    {
        return $this->belongsTo(ManualCategoryLevel2::class, 'id', 'level1');
    }
}
