<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ManualCategoryLevel2 extends Model
{
    use HasFactory;

    public function level1():HasOne
    {
        return $this->hasOne(ManualCategoryLevel1::class, 'level1', 'id');
    }
}
