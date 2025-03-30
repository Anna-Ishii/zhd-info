<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users_roles', function (Blueprint $table) {
            // 新しいカラムを追加
            $table->string('shop_code')->after('shop_id');
            $table->string('shop_name')->after('shop_code');

            // user_idカラムと外部キー制約を削除
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_roles', function (Blueprint $table) {
            // ロールバック時の処理
            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->dropColumn(['shop_code', 'shop_name']);
        });
    }
};
