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
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->boolean('is_workflow_terminal')->default(false)->after('is_terminal');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('station_statuses', function (Blueprint $table) {
            $table->dropColumn('is_workflow_terminal');
        });
    }
};
