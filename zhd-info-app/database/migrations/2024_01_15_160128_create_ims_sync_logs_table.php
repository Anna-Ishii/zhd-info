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
        Schema::create('ims_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('import_at');
            $table->dateTime('import_department_at')->nullable();
            $table->text('import_department_message')->nullable();
            $table->boolean('import_department_error')->nullable();
            $table->dateTime('import_crew_at')->nullable();
            $table->text('import_crew_message')->nullable();
            $table->boolean('import_crew_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ims_sync_logs');
    }
};
