<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWowTalkNotificationLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wowtalk_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type');                   // ログの種類（message か manual）
            $table->string('command_name');               // コマンド名
            $table->timestamp('started_at')->nullable();  // 開始時刻s
            $table->timestamp('finished_at')->nullable(); // 終了時刻
            $table->boolean('status')->default(false);    // ステータス
            $table->text('error_message')->nullable();    // エラーメッセージ
            $table->integer('attempts')->default(0);      // 試行回数
            $table->timestamps();                         // 作成日・更新日
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wowtalk_notification_logs');
    }
};
