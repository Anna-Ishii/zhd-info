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
        Schema::create('message_organization', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('organization1_id')->nullable();
            $table->unsignedBigInteger('organization2_id')->nullable();
            $table->unsignedBigInteger('organization3_id')->nullable();
            $table->unsignedBigInteger('organization4_id')->nullable();
            $table->unsignedBigInteger('organization5_id')->nullable();
            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('organization1_id')->references('id')->on('organization1');
            $table->foreign('organization2_id')->references('id')->on('organization2');
            $table->foreign('organization3_id')->references('id')->on('organization3');
            $table->foreign('organization4_id')->references('id')->on('organization4');
            $table->foreign('organization5_id')->references('id')->on('organization5');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_organization');
    }
};
