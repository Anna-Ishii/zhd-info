<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Traits\WhereLike;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
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

    public function organization1(): HasOne
    {
        return $this->hasOne(Organization1::class, 'id', 'organization1_id');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(MessageOrganization::class, foreignKey: 'id', ownerKey: 'message_id');
    }

    public function brand(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'message_brand', 'message_id', 'brand_id');
    }

    public function tag(): BelongsToMany
    {
        return $this->belongsToMany(MessageTagMaster::class, 'message_tags', 'message_id', 'tag_id');
    }

    public function content(): HasMany
    {
        return $this->hasMany(MessageContent::class);
    }

    public function brands_string($brandList = [])
    {
        // リレーションからnameプロパティを取得して配列に変換
        $brandNames = $this->brand()->orderBy('id', 'asc')->pluck('name')->toArray();
        if ($brandList === $brandNames) return "全業態";
        // カンマ区切りの文字列として返す
        return implode(',', $brandNames);
    }
    public function getBrandsStringAttribute()
    {
        // リレーションからnameプロパティを取得して配列に変換
        $brandNames = $this->brand()->orderBy('id', 'asc')->pluck('name')->toArray();
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

    // public function getViewRateAttribute() : float
    // {
    //     $user_count = $this->withCount('user')->get();
    //     $readed_user_count = $this->withCount('readed_user')->get();
    //     if($user_count == 0) return 0;

    //     return round((($readed_user_count / $user_count) * 100), 1);
    // }

    public function getContentFileSizeAttribute()
    {
        // 関連する全ての MessageContent インスタンスを取得
        $message_contents = $this->hasMany(MessageContent::class, 'message_id', 'id')->get();

        // 複数ファイルの場合の処理
        if ($message_contents->isNotEmpty()) {
            $total_filesize = 0;

            // 各 MessageContent インスタンスを繰り返し処理
            foreach ($message_contents as $message_content) {
                $content_url = $message_content->content_url;

                // content_url が設定されていない場合はスキップ
                if (!isset($content_url)) {
                    continue;
                }

                $path = public_path($content_url);

                // ファイルが存在しない場合はスキップ
                if (!file_exists($path)) {
                    continue;
                }

                $total_filesize += filesize($path);
            }

            $K = 1000;
            $M = 1000 * $K;

            if ($M <= $total_filesize) {
                return round($total_filesize / $M, 2) . "MB";
            } elseif ($K <= $total_filesize) {
                return round($total_filesize / $K, 2) . "KB";
            }

            return $total_filesize . "B";

        // 単一ファイルの場合の処理
        } else {

            if (!isset($this->content_url)) return "ファイルがありません";

            $path = public_path($this->content_url);

            if (!file_exists($path)) return "ファイルがありません";

            $filesize = filesize($path);

            $K = 1000;
            $M = 1000 * $K;

            if ($M <= $filesize) {
                return round($filesize / $M, 2) . "MB";
            } elseif ($K <= $filesize) {
                return round($filesize / $K, 2) . "KB";
            }

            return $filesize . "B";
        }
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

    public function scopeStartDatetimeFromDayAgo(Builder $query, $days)
    {
        // 特定の日数前から現在までの期間を計算
        $startDateTime = Carbon::now()->subDays($days);

        return $query
                ->where('start_datetime', '<=', now('Asia/Tokyo'))
                ->where(function ($q) {
                    $q->where('end_datetime', '>', now('Asia/Tokyo'))
                    ->orWhereNull('end_datetime');
                })
                ->where('editing_flg',
                    false
                );

        return $query->where('start_datetime', '>=', $startDateTime);
    }

    public function putCrewRead(array $crews = []) :Void
    {
        $params = [];
        $crews_unique = array_unique($crews);
        foreach ($crews_unique as $crew) {
            $exists = DB::table('crew_message_logs')
                ->where('crew_id', $crew)
                ->where('message_id', $this->attributes['id'])
                ->exists();
            if($exists) continue; // すでに既読してたら、登録しない
            $params[] = [
                'crew_id' => $crew,
                'message_id' => $this->attributes['id'],
                'readed_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        DB::table('crew_message_logs')->insert($params);
    }
}
