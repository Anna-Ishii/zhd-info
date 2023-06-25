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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('shop_code')->nullable();
            $table->unsignedBigInteger('organization4_id');
            $table->unsignedBigInteger('organization3_id');
            $table->unsignedBigInteger('organization2_id');
            $table->unsignedBigInteger('organization1_id');
            $table->foreign('organization4_id')->references('id')->on('organization4');
            $table->foreign('organization3_id')->references('id')->on('organization3');
            $table->foreign('organization2_id')->references('id')->on('organization2');
            $table->foreign('organization1_id')->references('id')->on('organization1');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
