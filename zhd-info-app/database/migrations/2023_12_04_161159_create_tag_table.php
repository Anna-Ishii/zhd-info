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
        Schema::create('manual_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('manual_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();

            $table->foreign('manual_id')->references('id')->on('manuals');
            $table->foreign('tag_id')->references('id')->on('manual_tag_master');

            $table->unique([
                'manual_id',
                'tag_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_tags');
    }
};
