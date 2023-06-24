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
            $table->string('email')->unique();
            $table->string('password');
            $table->string('employee_code')->unique();
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('roll_id');
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->foreign('roll_id')->references('id')->on('rolls');

            $table->timestamps();
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
