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
        Schema::table('messages', function (Blueprint $table) {
            //
            $table->string('title')->nullable(true)->change();
            $table->string('content_name')->nullable(true)->change();
            $table->string('content_url')->nullable(true)->change();
            $table->unsignedBigInteger('category_id')->nullable(true)->change();
            $table->unsignedBigInteger('organization1_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            //
        });
    }
};
