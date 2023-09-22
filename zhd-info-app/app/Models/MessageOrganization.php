<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MessageOrganization extends Model
{
    protected $table = 'message_organization';

    protected $fillable = [
        'message_id',
        'organization1_id',
        'organization2_id',
        'organization3_id',
        'organization4_id',
        'organization5_id',
    ];

}
