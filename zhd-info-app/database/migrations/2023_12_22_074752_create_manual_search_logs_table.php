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
        Schema::create('manual_search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->unsignedBigInteger('shop_id');
            $table->dateTime('searched_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_search_logs');
    }
};
