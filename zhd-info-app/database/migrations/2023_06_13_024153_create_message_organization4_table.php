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
        Schema::create('message_organization4', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('organization4_id');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('organization4_id')->references('id')->on('organization4');
            $table->unique(['message_id', 'organization4_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_organization4');
    }
};
