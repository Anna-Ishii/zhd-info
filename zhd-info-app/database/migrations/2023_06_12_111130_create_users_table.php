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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('belong_label');
            $table->string('email');
            $table->string('password');
            $table->string('employee_code');
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('roll_id');
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreign('roll_id')->references('id')->on('rolls');
            $table->unique(['email', 'shop_id']);
            $table->unique(['employee_code', 'shop_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
