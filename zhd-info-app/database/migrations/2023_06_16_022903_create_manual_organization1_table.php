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
        Schema::create('manual_organization1', function (Blueprint $table) {
            $table->unsignedBigInteger('manual_id');
            $table->unsignedBigInteger('organization1_id');
            $table->foreign('manual_id')->references('id')->on('manuals');
            $table->foreign('organization1_id')->references('id')->on('organization1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_organization1');
    }
};
