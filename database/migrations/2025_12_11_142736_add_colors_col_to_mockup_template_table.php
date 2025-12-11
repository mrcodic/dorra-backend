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
        Schema::table('mockup_template', function (Blueprint $table) {
            $table->json('colors')->nullable()->after('positions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mockup_template', function (Blueprint $table) {
            $table->dropColumn('colors');
        });
    }
};
