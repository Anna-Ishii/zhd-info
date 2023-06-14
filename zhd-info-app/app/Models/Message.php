<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable =
    [
        'title',
        'content_url',
        'category_id',
        'create_user',
        'status',
        'emergency_flg',
        'start_datatime',
        'end_datatime',
        'target_roll',
        'target_block',
    ];

    // 多対多のリレーションを定義
    public function roll(): BelongsToMany
    {
        return $this->belongsToMany(Roll::class, 'message_roll');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_user', 'employee_code', 'message_id')
            ->withPivot('read_flg', 'shop_id');
    }

    public function create_user_detail(): HasOne
    {
        return $this->hasOne(User::class, 'create_user', 'employee_code');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, foreignKey: 'id', localKey: 'category_id');
    }
}
