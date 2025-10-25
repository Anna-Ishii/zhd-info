<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualContent extends Model
{
    use SoftDeletes;

    protected $table = 'manualcontents';

    protected $fillable = [
        'id',
        'manual_id',
        'content_name',
        'content_url',
        'thumbnails_url',
        'title',
        'description',
        'order_no',
    ];

    public function getContentTypeAttribute()
    {
        $content_url = $this->attributes['content_url'];

        // 拡張子を取得
        $extension = pathinfo($content_url, PATHINFO_EXTENSION);

        return $extension;
    }

    public function getContentFileSizeAttribute()
    {
        if (!isset($this->content_url)) return "ファイルがありません";
        $path = public_path($this->content_url);

        if (!file_exists($path)) return "ファイルがありません";

        $filesize = filesize($path);

        $K = 1000;
        $M = 1000 * $K;

        if ($M <= $filesize) {
            return round($filesize / $M, 2) . "MB";
        } else if ($K <= $filesize) {
            return round($filesize / $K, 2) . "KB";
        }

        return $filesize . "B";
    }
}
