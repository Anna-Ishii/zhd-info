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
        Schema::create('message_user', function (Blueprint $table) {
            $table->string('employee_code');
            $table->integer('message_id');
            $table->boolean('read_flg');
            $table->integer('shop_id');
            $table->timestamps();

            $table->unique(['message_id', 'employee_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_user');
    }
};
