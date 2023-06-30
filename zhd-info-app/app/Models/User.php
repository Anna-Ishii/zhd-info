<?php

namespace App\Models;

use App\Models\Traits\WhereLike;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use WhereLike;
    use SoftDeletes;
    
    protected $fillable = 
        ['name',
         'belong_label',
         'email',
         'password',
         'employee_code',
         'shop_id',
         'roll_id',
        ];

    public function message(): BelongsToMany
    {
        return $this->belongsToMany(Message::class,'message_user','user_id', 'message_id')
                    ->withPivot('read_flg', 'shop_id');
    }

    public function manual(): BelongsToMany
    {
        return $this->belongsToMany(Manual::class, 'manual_user','user_id', 'manual_id')
                    ->withPivot('read_flg', 'shop_id');
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class, 'roll_id', 'id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
