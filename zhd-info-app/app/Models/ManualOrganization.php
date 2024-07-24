<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ManualOrganization extends Model
{
    protected $table = 'manual_organization';

    protected $fillable = [
        'manual_id',
        'organization1_id',
        'organization2_id',
        'organization3_id',
        'organization4_id',
        'organization5_id',
    ];

}
