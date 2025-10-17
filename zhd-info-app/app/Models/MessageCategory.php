<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MessageCategory extends Model
{
    protected $table = 'message_categories';

    protected $fillable = ['name'];

    // public function message(): BelongsToMany
    // {
    //     return $this->belongsToMany(Message::class, 'message_category', 'category_id','message_id');
    // }
}
