<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $primaryKey = 'employee_code';

    protected $fillable = 
        ['name',
         'email',
         'password',
         'employee_code',
         'shop_id',
         'roll_id'
        ];

    public function message(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_user', 'employee_code', 'message_id')
                    ->withPivot('read_flg', 'shop_id');
    }
}
