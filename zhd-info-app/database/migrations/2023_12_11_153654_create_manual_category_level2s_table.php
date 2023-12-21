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
        Schema::create('manual_category_level2s', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('level1');
            $table->foreign('level1')->references('id')->on('manual_category_level1s');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_category_level2s');
    }
};
