<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageContent extends Model
{
    protected $table = 'message_contents';

    protected $fillable = [
        'id',
        'message_id',
        'content_name',
        'content_url',
        'thumbnails_url',
        'join_flg'
    ];
}
