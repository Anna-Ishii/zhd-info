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
        Schema::rename('wowtalk_recipients', 'incident_notifications_recipients');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('incident_notifications_recipients', 'wowtalk_recipients');
    }
};
