<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Traits\WhereLike;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    use WhereLike;

    protected $table = 'messages';

    protected $fillable =
    [
        'title',
        'content_name',
        'content_url',
        'thumbnails_url',
        'category_id',
        'create_admin_id',
        'emergency_flg',
        'editing_flg',
        'organization1_id',
        'number',
        'updated_admin_id',
        'start_datetime',
        'end_datetime',

    ];

    protected $casts = [
        'emergency_flg' => 'boolean',
        'editing_flg' => 'boolean',
    ];

    // 多対多のリレーションを定義
    public function roll(): BelongsToMany
    {
        return $this->belongsToMany(Roll::class, 'message_roll');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_user','message_id', 'user_id')
            ->using(MessageUser::class)
            ->withPivot('read_flg', 'shop_id', 'readed_datetime');
    }
    public function readed_user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_user', 'message_id', 'user_id')
            ->using(MessageUser::class)
            ->wherePivot('read_flg', true)
            ->withPivot('read_flg', 'shop_id', 'readed_datetime');
    }

    public function shop(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'message_user', 'message_id', 'shop_id');
    }

    public function create_user(): HasOne
    {
        return $this->hasOne(Admin::class, 'id', 'create_admin_id')->withTrashed();
    }

    public function updated_user(): HasOne
    {
        return $this->hasOne(Admin::class, 'id', 'updated_admin_id')->withTrashed();
    }

    public function category(): HasOne
    {
        return $this->hasOne(MessageCategory::class, foreignKey: 'id', localKey: 'category_id');
    }

    public function organization5(): BelongsToMany
    {
        return $this->belongsToMany(Organization5::class, 'message_organization5', 'message_id', 'organization5_id');
    }

    public function organization4(): BelongsToMany
    {
        return $this->belongsToMany(Organization4::class, 'message_organization4', 'message_id', 'organization4_id');
    }

    public function brand(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'message_brand', 'message_id', 'brand_id');
    }

    public function brands_string($brandList = [])
    {
        // リレーションからnameプロパティを取得して配列に変換
        $brandNames = $this->brand()->orderBy('id', 'asc')->pluck('name')->toArray();
        if ($brandList === $brandNames) return "全業態";
        // カンマ区切りの文字列として返す
        return implode(',', $brandNames);
    }

    public function getStatusAttribute()
    {
        if ($this->attributes['editing_flg'] == true)
            return $status =  PublishStatus::Editing;

        $start_datetime =
            !empty($this->attributes['start_datetime']) ? Carbon::parse($this->attributes['start_datetime'], 'Asia/Tokyo') : null;
        $end_datetime =
            !empty($this->attributes['end_datetime']) ? Carbon::parse($this->attributes['end_datetime'], 'Asia/Tokyo') : null;

        $now = Carbon::now('Asia/Tokyo');

        $status = PublishStatus::Wait;

        if (isset($start_datetime)) {
            if ($start_datetime->lte($now)) {
                $status = PublishStatus::Publishing;
            }
        }

        if (isset($end_datetime)) {
            if ($end_datetime->lte($now)) {
                $status = PublishStatus::Published;
            }
        }

        return $status;
    }

    public function getFormattedCreatedAtAttribute()
    {
        $before_datetime = $this->attributes['created_at'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }

    public function getFormattedUpdatedAtAttribute()
    {
        $before_datetime = $this->attributes['updated_at'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }

    public function getFormattedStartDatetimeAttribute()
    {
        $before_datetime = $this->attributes['start_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }

    public function getFormattedEndDatetimeAttribute()
    {
        $before_datetime = $this->attributes['end_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }
    
    public function getViewRateAttribute() : float
    {
        $user_count = $this->withCount('user')->get();
        $readed_user_count = $this->withCount('readed_user')->get();
        if($user_count == 0) return 0;

        return round((($readed_user_count / $user_count) * 100), 1);
    }

    // 待機
    public function scopeWaitMessage($query)
    {
        return $query
                ->where('end_datetime', '>', now('Asia/Tokyo'))
                ->where(function ($query) {
                    $query->where('start_datetime', '>', now('Asia/Tokyo'))
                    ->orWhereNull('start_datetime');
                })
                ->orWhereNull('end_datetime')
                ->where(function ($query) {
                    $query->where('start_datetime', '>', now('Asia/Tokyo'))
                    ->orWhereNull('start_datetime');
                })
                ->where('editing_flg', false);
    }

    // 掲載中
    public function scopePublishingMessage($query)
    {
        return $query
                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                ->where(function ($q) {
                    $q->where('end_datetime', '>', now('Asia/Tokyo'))
                        ->orWhereNull('end_datetime');
                })
                ->where('editing_flg', false);
    }

    // 掲載終了
    public function scopePublishedMessage($query)
    {
        return $query
                ->where('end_datetime', '<=', now('Asia/Tokyo'))
                ->where('editing_flg', false);
    }
    
    public function scopeViewRateBetween($query, $min = 0, $max = 100)
    {
        $min = isset($min) ? $min : 0;
        $max = isset($max) ? $max : 100;
        $query->havingRaw('ROUND((read_users / total_users) * 100, 2) BETWEEN ? AND ?', [$min, $max]);
    }
}
