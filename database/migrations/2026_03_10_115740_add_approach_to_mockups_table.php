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
            $table->string('approach')->after('area_top')->default('with_editor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->dropColumn('approach');
        });
    }
};
