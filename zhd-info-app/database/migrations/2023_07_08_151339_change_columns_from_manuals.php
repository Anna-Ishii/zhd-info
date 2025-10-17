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
            $table->unsignedBigInteger('organization1_id');
            $table->foreign('organization1_id')->references('id')->on('organization1');
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
