<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageViewRate extends Model
{
    protected $table = 'message_view_rates';

    protected $fillable = [
        'id',
        'message_id',
        'view_rate',
        'read_users',
        'total_users',
        'created_at',
        'updated_at'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
