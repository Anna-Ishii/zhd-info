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
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('employee_code');
            $table->unsignedBigInteger('organization1_id');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['email', 'organization1_id']);
            $table->unique(['employee_code', 'organization1_id']);

            $table->foreign('organization1_id')->references('id')->on('organization1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
