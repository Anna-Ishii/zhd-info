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
        Schema::table('manuals', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('category_level1_id')->nullable(true);
            $table->unsignedBigInteger('category_level2_id')->nullable(true);
            $table->foreign('category_level1_id')->references('id')->on('manual_category_level1s');
            $table->foreign('category_level2_id')->references('id')->on('manual_category_level2s');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manuals', function (Blueprint $table) {
            //
        });
    }
};
