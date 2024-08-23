<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WowTalkNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'wowtalk_notification_logs';

    protected $fillable =
    [
        'log_type',                // ログの種類（message か manual）
        'command_name',            // コマンド名
        'started_at',              // 開始時刻
        'finished_at',             // 終了時刻
        'status',                  // ステータス（成功か失敗か）
        'error_message',           // エラーメッセージ
        'attempts',                // 試行回数
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'status' => 'boolean',
    ];

    public function is_error(): bool {
        return !$this->status;
    }
}
