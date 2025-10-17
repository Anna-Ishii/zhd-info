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
        Schema::table('admin_recipients', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->after('id');
            $table->string('name')->nullable()->after('employee_number');
            $table->unsignedBigInteger('organization1_id')->after('name');
            $table->foreign('organization1_id')->references('id')->on('organization1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_recipients', function (Blueprint $table) {
            $table->dropColumn('employee_number');
            $table->dropColumn('name');
            $table->dropForeign(['organization1_id']);
            $table->dropColumn('organization1_id');
        });
    }
};
