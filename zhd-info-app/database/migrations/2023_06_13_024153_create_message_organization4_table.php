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
            $table->integer('message_id');
            $table->integer('organization4');
            $table->timestamps();

            $table->unique(['message_id', 'organization4']);
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
