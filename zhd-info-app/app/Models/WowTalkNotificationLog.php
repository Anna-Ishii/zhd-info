<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WowTalkNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'wowtalk_notification_logs';

    protected $fillable =
    [
        'log_type',
        'command_name',
        'started_at',
        'finished_at',
        'status',
        'error_message',
        'attempts',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function is_error(): bool {
        return !$this->status;
    }
}
