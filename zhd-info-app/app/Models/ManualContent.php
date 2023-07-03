<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manualcontent extends Model
{
    use SoftDeletes;
    
    protected $table = 'manualcontents';

    protected $fillable = [
        'manual_id',
        'content_name',
        'content_url',
        'thumbnails',
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
}
