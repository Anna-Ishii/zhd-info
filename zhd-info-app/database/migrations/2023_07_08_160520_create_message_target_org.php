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
        Schema::create('message_brand', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_brand', function (Blueprint $table) {
            //
        });
    }
};
