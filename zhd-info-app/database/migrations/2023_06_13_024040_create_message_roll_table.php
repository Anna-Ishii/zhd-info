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
        Schema::create('message_roll', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('roll_id');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('roll_id')->references('id')->on('rolls');
            $table->unique(['message_id', 'roll_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_rolls');
    }
};
