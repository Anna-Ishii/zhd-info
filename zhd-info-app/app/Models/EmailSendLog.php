<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSendLog extends Model
{
    use HasFactory;

    protected $table = 'email_send_logs';

    protected $fillable =
    [
        'email',
        'subject',
        'command_name',
        'started_at',
        'finished_at',
        'status',
        'error_message'
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
