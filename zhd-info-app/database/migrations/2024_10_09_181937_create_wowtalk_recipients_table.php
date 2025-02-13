<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWowtalkRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wowtalk_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('target')->default(true); // 通知を受け取るかどうかのフラグ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wowtalk_recipients');
    }
}
