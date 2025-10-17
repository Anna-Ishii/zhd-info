<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTagMaster extends Model
{
    protected $table = 'message_tag_master';

    protected $fillable = [
        'id',
        'name'
    ];
}