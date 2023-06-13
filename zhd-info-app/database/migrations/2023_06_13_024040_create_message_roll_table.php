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
            $table->integer('message_id');
            $table->integer('roll_id');
            $table->timestamps();

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
