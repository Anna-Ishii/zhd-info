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
        // このマイグレーションは無効化されています
        // users_rolesテーブル作成時に既に必要なカラムが定義されているため
        /*
        Schema::table('users_roles', function (Blueprint $table) {
            // shop_codeとshop_nameカラムが存在しない場合のみ追加
            if (!Schema::hasColumn('users_roles', 'shop_code')) {
                $table->string('shop_code')->after('shop_id');
            }
            if (!Schema::hasColumn('users_roles', 'shop_name')) {
                $table->string('shop_name')->after('shop_code');
            }

            // user_idカラムと外部キー制約を削除（存在する場合のみ）
            if (Schema::hasColumn('users_roles', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
        */
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
