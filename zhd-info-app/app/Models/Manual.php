<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery\Matcher\HasKey;

class Manual extends Model
{
    protected $table = 'manuals';

    protected $fillable =
    [
        'title',
        'description',
        'category_id',
        'content_url',
        'create_user_id',
        'category_id',
        'status',
        'start_datetime',
        'end_datetime',
        'target_block',
    ];

    // 多対多のリレーションを定義
    // public function roll(): BelongsToMany
    // {
    //     return $this->belongsToMany(Roll::class, 'message_roll');
    // }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manual_user', 'manual_id', 'user_id')
            ->withPivot('read_flg', 'shop_id');
    }

    public function create_user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'create_user_id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Manualcategory::class, foreignKey: 'id', localKey: 'category_id');
    }

    public function organization1(): BelongsToMany
    {
        return $this->BelongsToMany(Organization1::class, 'manual_organization1', 'manual_id', 'organization1');
    }

    public function content(): HasMany
    {
        return $this->hasMany(Manualcontent::class);
    }
}
