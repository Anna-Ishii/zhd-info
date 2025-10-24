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
        Schema::create('manual_manual_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_id')->constrained('manuals')->onDelete('cascade');
            $table->foreignId('manual_type_id')->constrained('manual_types')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['manual_id', 'manual_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_manual_type');
    }
};
