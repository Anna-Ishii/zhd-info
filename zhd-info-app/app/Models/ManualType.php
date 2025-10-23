<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * この形式を持つマニュアル
     */
    public function manuals()
    {
        return $this->belongsToMany(Manual::class);
    }
}