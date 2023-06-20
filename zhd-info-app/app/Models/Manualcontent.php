<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Manualcontent extends Model
{
    protected $table = 'manualcontents';

    protected $fillable = [
        'manual_id',
        'content_url',
        'title',
        'description',
        'order_no',
        'is_deleted'
    ];

    public function getContentTypeAttribute()
    {
        $content_url = $this->attributes['content_url']; // 'parameter'は実際のデータベースカラム名に置き換えてください

        // 拡張子を取得
        $extension = pathinfo($content_url, PATHINFO_EXTENSION);

        return $extension;
    }
}
