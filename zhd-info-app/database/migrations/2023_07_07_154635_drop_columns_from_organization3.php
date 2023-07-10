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
        Schema::table('organization3', function (Blueprint $table) {
            $table->dropForeign('organization3_organization2_id_foreign');
            $table->dropColumn('organization2_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization3', function (Blueprint $table) {
            //
        });
    }
};
