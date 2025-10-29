<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // level2 に SoftDeletes 追加
        if (Schema::hasTable('manual_category_level2s')) {
            Schema::table('manual_category_level2s', function (Blueprint $table) {
                if (!Schema::hasColumn('manual_category_level2s', 'deleted_at')) {
                    $table->softDeletes()->comment('Soft delete timestamp');
                    $table->index('deleted_at');
                }
            });
        }

        // 念のため level1 も揃える
        if (Schema::hasTable('manual_category_level1s')) {
            Schema::table('manual_category_level1s', function (Blueprint $table) {
                if (!Schema::hasColumn('manual_category_level1s', 'deleted_at')) {
                    $table->softDeletes()->comment('Soft delete timestamp');
                    $table->index('deleted_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('manual_category_level2s') && Schema::hasColumn('manual_category_level2s', 'deleted_at')) {
            Schema::table('manual_category_level2s', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('manual_category_level1s') && Schema::hasColumn('manual_category_level1s', 'deleted_at')) {
            Schema::table('manual_category_level1s', function (Blueprint $table) {
                $table->dropIndex(['deleted_at']);
                $table->dropSoftDeletes();
            });
        }
    }
};
