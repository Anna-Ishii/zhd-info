<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Manualcontent extends Model
{
    protected $table = 'manualcontents';

    protected $fillable = [
        'manual_id',
        'content_url',
        'title',
        'description',
        'order_no',
        'is_deleted'
    ];

    // public function message(): BelongsToMany
    // {
    //     return $this->belongsToMany(Message::class, 'message_category', 'category_id','message_id');
    // }
}
