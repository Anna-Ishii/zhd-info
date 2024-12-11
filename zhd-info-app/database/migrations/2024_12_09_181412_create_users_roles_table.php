<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id');

            // 役割ごとのカラムを追加
            $table->unsignedBigInteger('DM_id')->nullable();
            $table->string('DM_name')->nullable();
            $table->string('DM_email')->unique()->nullable();
            $table->boolean('DM_view_notification')->default(false);

            $table->unsignedBigInteger('BM_id')->nullable();
            $table->string('BM_name')->nullable();
            $table->string('BM_email')->unique()->nullable();
            $table->boolean('BM_view_notification')->default(false);

            $table->unsignedBigInteger('AM_id')->nullable();
            $table->string('AM_name')->nullable();
            $table->string('AM_email')->unique()->nullable();
            $table->boolean('AM_view_notification')->default(false);

            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_roles');
    }
};
