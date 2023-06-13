<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = 
        ['title',
         'content_url', 
         'create_user',
         'status',
         'is_emergency',
         'start_datatime',
         'end_datatime',
         'target_roll',
         'target_block',
        ];

    // 多対多のリレーションを定義
    public function roll(): BelongsToMany
    {
        return $this->belongsToMany(Roll::class, 'message_roll');
    }

}
