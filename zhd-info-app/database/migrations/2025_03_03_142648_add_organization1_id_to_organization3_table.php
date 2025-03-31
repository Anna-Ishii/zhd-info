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
        // ユニーク制約を削除
        Schema::table('organization3', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });

        Schema::table('organization3', function (Blueprint $table) {
            $table->unsignedBigInteger('organization1_id')->nullable()->before('order_no');
        });

        Schema::table('organization3', function (Blueprint $table) {
            $table->foreign('organization1_id')->references('id')->on('organization1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization3', function (Blueprint $table) {
            $table->dropForeign(['organization1_id']);
            $table->dropColumn('organization1_id');
        });

        // ロールバック時にユニーク制約を戻す
        Schema::table('organization3', function (Blueprint $table) {
            $table->unique(['name']);
        });
    }
};
