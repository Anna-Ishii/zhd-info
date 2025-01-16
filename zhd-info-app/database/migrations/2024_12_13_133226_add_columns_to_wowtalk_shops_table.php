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
        Schema::table('wowtalk_shops', function (Blueprint $table) {
            if (!Schema::hasColumn('wowtalk_shops', 'business_notification1')) {
                $table->boolean('business_notification1')->default(false)->after('notification_target1');
            }
            $table->string('wowtalk2_id')->nullable()->after('business_notification1');
            $table->boolean('notification_target2')->default(false)->after('wowtalk2_id');
            $table->boolean('business_notification2')->default(false)->after('notification_target2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wowtalk_shops', function (Blueprint $table) {
            if (Schema::hasColumn('wowtalk_shops', 'business_notification1')) {
                $table->dropColumn('business_notification1');
            }
            if (Schema::hasColumn('wowtalk_shops', 'wowtalk2_id')) {
                $table->dropColumn('wowtalk2_id');
            }
            if (Schema::hasColumn('wowtalk_shops', 'notification_target2')) {
                $table->dropColumn('notification_target2');
            }
            if (Schema::hasColumn('wowtalk_shops', 'business_notification2')) {
                $table->dropColumn('business_notification2');
            }
        });
    }
};
