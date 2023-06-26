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
            $table->string('content_name');
            $table->string('content_url');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('create_admin_id');
            $table->integer('status')->default(0);
            $table->boolean('emergency_flg')->default(false);
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->foreign('category_id')->references('id')->on('message_categories');
            $table->foreign('create_admin_id')->references('id')->on('admin');
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
