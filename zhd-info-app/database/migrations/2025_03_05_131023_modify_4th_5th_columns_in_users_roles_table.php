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
            // 4thの情報をAM_view_notificationの後に追加
            $table->unsignedBigInteger('4th_id')->nullable()->after('AM_view_notification');
            $table->string('4th_name')->nullable()->after('4th_id');
            $table->string('4th_email')->nullable()->after('4th_name');
            $table->boolean('4th_view_notification')->default(false)->after('4th_email');

            // 5thの情報を4th_view_notificationの後に追加
            $table->unsignedBigInteger('5th_id')->nullable()->after('4th_view_notification');
            $table->string('5th_name')->nullable()->after('5th_id');
            $table->string('5th_email')->nullable()->after('5th_name');
            $table->boolean('5th_view_notification')->default(false)->after('5th_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_roles', function (Blueprint $table) {
            $table->dropColumn(['4th_id', '4th_name', '4th_email', '4th_view_notification', '5th_id', '5th_name', '5th_email', '5th_view_notification']);
        });
    }
};
