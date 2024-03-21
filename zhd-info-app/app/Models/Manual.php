<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Traits\WhereLike;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery\Matcher\HasKey;

class Manual extends Model
{
    use WhereLike;
    
    protected $table = 'manuals';

    protected $fillable =
    [
        'title',
        'description',
        'category_level1_id',
        'category_level2_id',
        'content_name',
        'content_url',
        'thumbnails_url',
        'create_admin_id',
        'editing_flg',
        'organization1_id',
        'number',
        'updated_admin_id',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'editing_flg' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime'
    ];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manual_user', 'manual_id', 'user_id')
            ->using(ManualUser::class)
            ->withPivot('read_flg','shop_id', 'readed_datetime');
    }

    public function readed_user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manual_user', 'manual_id', 'user_id')
            ->using(ManualUser::class)
            ->wherePivot('read_flg', true)
            ->withPivot('read_flg','shop_id', 'readed_datetime');
    }

    public function shop(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'manual_user', 'manual_id', 'shop_id');
    }

    public function create_user(): HasOne
    {
        return $this->hasOne(Admin::class, 'id', 'create_admin_id')->withTrashed();
    }

    public function updated_user(): HasOne
    {
        return $this->hasOne(Admin::class, 'id', 'updated_admin_id')->withTrashed();
    }

    public function category_level1(): HasOne
    {
        return $this->hasOne(ManualCategoryLevel1::class, foreignKey: 'id', localKey: 'category_level1_id');
    }

    public function category_level2(): HasOne
    {
        return $this->hasOne(ManualCategoryLevel2::class, foreignKey: 'id', localKey: 'category_level2_id');
    }

    public function organization1(): HasOne
    {
        return $this->hasOne(Organization1::class, 'id', 'organization1_id');
    }

    public function brand(): BelongsToMany
    {
        return $this->BelongsToMany(Brand::class, 'manual_brand', 'manual_id', 'brand_id');
    }

    public function tag(): BelongsToMany
    {
        return $this->belongsToMany(ManualTagMaster::class, 'manual_tags', 'manual_id', 'tag_id');
    }

    public function brands_string($brandList = [])
    {
        // リレーションからnameプロパティを取得して配列に変換
        $brandNames = $this->brand()->orderBy('id', 'asc')->pluck('name')->toArray();
        if($brandList === $brandNames) return "全業態";
        // カンマ区切りの文字列として返す
        return implode(',', $brandNames);
    }

    public function content(): HasMany
    {
        return $this->hasMany(ManualContent::class);
    }

    public function getStatusAttribute()
    {
        if ($this->attributes['editing_flg'] == true)
            return $status = PublishStatus::Editing;

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

    public function getContentTypeAttribute()
    {
        $content_url = $this->attributes['content_url']; // 'parameter'は実際のデータベースカラム名に置き換えてください

        // 拡張子を取得
        $extension = pathinfo($content_url, PATHINFO_EXTENSION);

        return $extension;
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

    public function getFormattedStartDatetimeForExportAttribute()
    {
        $before_datetime = $this->attributes['start_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD HH:mm') : null;
    }


    public function getFormattedEndDatetimeAttribute()
    {
        $before_datetime = $this->attributes['end_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }

    public function getFormattedEndDatetimeForExportAttribute()
    {
        $before_datetime = $this->attributes['end_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD HH:mm') : null;
    }
    // public function getViewRateAttribute(): float
    // {
    //     $user_count = $this->withCount('user')->get();
    //     $readed_user_count = $this->withCount('readed_user')->get();
    //     if ($user_count == 0) return 0;

    //     return round((($readed_user_count / $user_count) * 100), 1);
    // }

    public function getContentFileSizeAttribute()
    {
        if(!isset($this->content_url)) return "ファイルがありません";
        $path = public_path($this->content_url);

        if (!file_exists($path)) return "ファイルがありません";

        $filesize = filesize($path);

        $K = 1000;
        $M = 1000 * $K;

        if ($M <= $filesize) {
            return round($filesize / $M, 2)."MB";
        } else if ($K <= $filesize) {
            return round($filesize / $K, 2)."KB";
        }

        return $filesize."B";
    }

    // 待機
    public function scopeWaitManual($query)
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
    public function scopePublishingManual($query)
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
    public function scopePublishedManual($query)
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

    public function scopeStartDatetimeFromDayAgo(Builder $query, $days)
    {
        // 特定の日数前から現在までの期間を計算
        $startDateTIme = Carbon::now()->subDays($days);

        $query->where('start_datetime', '>=', $startDateTime);
    }

    //  新着件数
    public function scopeRecentPublishing($query)
    {
        $start_date_time = Carbon::now()->subDays(7)->startOfDay();

        return $query
            ->whereBetween('start_datetime', [$start_date_time, now('Asia/Tokyo')])
            ->where('start_datetime', '<=', now('Asia/Tokyo'))
            ->where(function ($q) {
                $q->where('end_datetime', '>', now('Asia/Tokyo'))
                ->orWhereNull('end_datetime');
            })
            ->where('editing_flg', false);
    }

    public static function getCurrentNumber($organization1_id): Int{
        return self::where('organization1_id', $organization1_id)->max('number') ?? 0;
    }
}
