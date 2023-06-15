<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable =
    [
        'title',
        'content_url',
        'category_id',
        'create_user_id',
        'status',
        'emergency_flg',
        'start_datetime',
        'end_datetime',
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
        return $this->belongsToMany(User::class, 'message_user','message_id', 'user_id')
            ->withPivot('read_flg', 'shop_id');
    }

    public function create_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'create_user_id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, foreignKey: 'id', localKey: 'category_id');
    }

    public function organization4(): BelongsToMany
    {
        return $this->BelongsToMany(Organization4::class, 'message_organization4', 'message_id', 'organization4');
    }
}
