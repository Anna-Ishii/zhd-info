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
        Schema::create('crew_message_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('crew_id');
            $table->unsignedBigInteger('message_id');
            $table->dateTime('readed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crew_message_logs');
    }
};
