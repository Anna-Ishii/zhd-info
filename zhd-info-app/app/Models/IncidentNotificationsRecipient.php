<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentNotificationsRecipient extends Model
{
    protected $table = 'incident_notifications_recipients';

    protected $fillable = [
        'id',
        'email',
        'target',
        'created_at',
        'updated_at',
    ];
}
