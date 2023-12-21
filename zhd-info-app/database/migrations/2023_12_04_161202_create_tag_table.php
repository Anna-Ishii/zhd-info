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
        Schema::create('message_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('tag_id')->references('id')->on('message_tag_master');

            $table->unique([
                'message_id',
                'tag_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_tags');
    }
};
