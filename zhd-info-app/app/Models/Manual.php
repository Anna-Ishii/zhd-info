<?php

namespace App\Models;

use App\Models\Traits\WhereLike;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'category_id',
        'content_name',
        'content_url',
        'thumbnails_url',
        'create_admin_id',
        'category_id',
        'organization1_id',
        'number',
        'updated_admin_id',
        'start_datetime',
        'end_datetime',
    ];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manual_user', 'manual_id', 'user_id')
            ->withPivot('read_flg', 'shop_id');
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
        return $this->hasOne(ManualCategory::class, foreignKey: 'id', localKey: 'category_id');
    }

    public function brand(): BelongsToMany
    {
        return $this->BelongsToMany(Brand::class, 'manual_brand', 'manual_id', 'brand_id');
    }

    public function brands_string($brandList = [])
    {
        $brands = $this->brand();
        // リレーションからnameプロパティを取得して配列に変換
        $brandNames = $brands->orderBy('id', 'asc')->pluck('name')->toArray();
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

    public function getFormattedEndDatetimeAttribute()
    {
        $before_datetime = $this->attributes['end_datetime'];
        Carbon::setLocale('ja');
        return $before_datetime ? Carbon::parse($before_datetime)->isoFormat('YYYY/MM/DD(ddd) HH:mm') : null;
    }
}
