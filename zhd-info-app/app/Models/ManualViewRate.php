<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualViewRate extends Model
{
    protected $table = 'manual_view_rates';

    protected $fillable = [
        'id',
        'manual_id',
        'organization1_id',
        'view_rate',
        'read_users',
        'total_users',
        'created_at',
        'updated_at'
    ];

    public function manual()
    {
        return $this->belongsTo(Manual::class);
    }
}
