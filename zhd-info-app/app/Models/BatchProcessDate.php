<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchProcessDate extends Model
{
    protected $table = 'batch_process_dates';

    protected $fillable = [
        'id',
        'organization1_id',
        'process_name',
        'execution_date',
        'created_at',
        'updated_at',
    ];
}
