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
        Schema::create('manual_brand', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('manual_id');
            $table->unsignedBigInteger('brand_id');
            $table->foreign('manual_id')->references('id')->on('manuals');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_brand', function (Blueprint $table) {
            //
        });
    }
};
