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
        Schema::table('bulk_job_items', function (Blueprint $table) {
            $table->string('color')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        DB::table('bulk_job_items')
            ->whereNull('color')
            ->update(['color' => '']);

        Schema::table('bulk_job_items', function (Blueprint $table) {
            $table->string('color')->nullable(false)->change();
        });
    }
};
