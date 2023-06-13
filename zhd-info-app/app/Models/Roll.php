<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Roll extends Model
{
    protected $table = 'rolls';

    protected $fillable = ['name'];

    public function message(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_roll');
    }
}
