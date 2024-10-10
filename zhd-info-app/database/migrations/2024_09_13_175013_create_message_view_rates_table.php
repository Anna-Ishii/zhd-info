<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageViewRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_view_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('organization1_id');
            $table->decimal('view_rate', 4, 1)->nullable(); // 閲覧率
            $table->unsignedBigInteger('read_users')->default(0); // 既読ユーザー数
            $table->unsignedBigInteger('total_users')->default(0); // 全体ユーザー数
            $table->timestamps(); // created_at, updated_at

            $table->foreign('message_id')->references('id')->on('messages');
            $table->foreign('organization1_id')->references('id')->on('organization1');
            $table->unique(['message_id', 'organization1_id']); // 複合ユニーク制約を追加
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_view_rates');
    }
}
