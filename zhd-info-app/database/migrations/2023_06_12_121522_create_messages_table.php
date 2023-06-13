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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('content_url');
            $table->integer('create_user');
            $table->integer('status');
            $table->boolean('is_emergency');
            $table->dateTime('start_datatime');
            $table->dateTime('end_datatime');
            $table->integer('target_roll');
            $table->integer('target_block');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
