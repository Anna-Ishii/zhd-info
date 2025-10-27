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
        Schema::table('messages', function (Blueprint $table) {
            $table->tinyInteger('instruction_flg')->default(0)->comment('指示フラグ: 1=指示あり, 0=指示なし');
            $table->string('instruction_id', 255)->nullable()->comment('指示ID');
            $table->string('instruction_title', 255)->nullable()->comment('指示タイトル');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['instruction_flg', 'instruction_id', 'instruction_title']);
        });
    }
};
