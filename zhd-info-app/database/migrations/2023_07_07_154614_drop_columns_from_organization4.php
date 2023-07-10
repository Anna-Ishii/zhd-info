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
        Schema::table('organization4', function (Blueprint $table) {
            $table->dropForeign('organization4_organization3_id_foreign');
            $table->dropColumn('organization3_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organization4', function (Blueprint $table) {
            //
        });
    }
};
