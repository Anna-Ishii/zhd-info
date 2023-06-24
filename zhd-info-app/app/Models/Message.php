<?php

namespace App\Models;

use Carbon\Carbon;
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
        return $this->belongsToMany(Organization4::class, 'message_organization4', 'message_id', 'organization4');
    }

    public function getStatusAttribute()
    {
        $start_datetime =
            !empty($this->attributes['start_datetime']) ? Carbon::parse($this->attributes['start_datetime'], 'Asia/Tokyo') : null;
        $end_datetime =
            !empty($this->attributes['end_datetime']) ? Carbon::parse($this->attributes['end_datetime'], 'Asia/Tokyo') : null;

        $now = Carbon::now('Asia/Tokyo');

        $status = [
            'id'   => 0,
            'name' => '待機'
        ];

        if (isset($start_datetime)) {
            if ($start_datetime->lte($now)) {
                $status = [
                    'id'   => 1,
                    'name' => '掲載中'
                ];
            }
        }

        if (isset($end_datetime)) {
            if ($end_datetime->lte($now)) {
                $status = [
                    'id'   => 2,
                    'name' => '掲載終了'
                ];
            }
        }

        return $status;
    }

}
