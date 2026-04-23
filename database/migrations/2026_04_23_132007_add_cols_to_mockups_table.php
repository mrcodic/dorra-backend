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
        Schema::table('mockups', function (Blueprint $table) {
            $table->after('fill_ratio',function (Blueprint $table) {
                $table->unsignedInteger('light_strength');
                $table->unsignedInteger('shadow_strength');
                $table->unsignedInteger('displacement_scale');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->dropColumn(['light_strength','shadow_strength','displacement_scale']);
        });
    }
};
