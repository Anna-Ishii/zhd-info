<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $fillable = 
        ['name',
         'belong_label',
         'email',
         'password',
         'employee_code',
         'shop_id',
         'roll_id'
        ];

    public function message(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_user', 'user_id', 'message_id')
                    ->withPivot('read_flg', 'shop_id');
    }
}
