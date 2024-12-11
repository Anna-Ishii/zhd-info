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
            if (Schema::hasColumn('wowtalk_shops', 'wowtalk_id')) {
                $table->renameColumn('wowtalk_id', 'wowtalk1_id');
            }
            if (Schema::hasColumn('wowtalk_shops', 'notification_target')) {
                $table->renameColumn('notification_target', 'notification_target1');
            }
            $table->string('wowtalk2_id')->nullable()->after('wowtalk1_id');
            $table->boolean('notification_target2')->default(false)->after('notification_target1');
            $table->boolean('business_notification1')->default(false)->after('notification_target2');
            $table->boolean('business_notification2')->default(false)->after('business_notification1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wowtalk_shops', function (Blueprint $table) {
            if (Schema::hasColumn('wowtalk_shops', 'wowtalk1_id')) {
                $table->renameColumn('wowtalk1_id', 'wowtalk_id');
            }
            if (Schema::hasColumn('wowtalk_shops', 'notification_target1')) {
                $table->renameColumn('notification_target1', 'notification_target');
            }
            $table->dropColumn('wowtalk2_id');
            $table->dropColumn('notification_target2');
            $table->dropColumn('business_notification1');
            $table->dropColumn('business_notification2');
        });
    }
};
