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
        Schema::create('manualcontents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manual_id');
            $table->string('content_name');
            $table->string('content_url');
            $table->string('title');
            $table->text('description')->nullable(true);
            $table->integer('order_no');
            $table->foreign('manual_id')->references('id')->on('manuals');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manualcontents');
    }
};
