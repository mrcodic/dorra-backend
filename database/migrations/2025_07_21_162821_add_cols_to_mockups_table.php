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
            $table->after('colors',function (Blueprint $table) {
                $table->float('area_width')->nullable();
                $table->float('area_height')->nullable();
                $table->float('area_left')->nullable();
                $table->float('area_top')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            //
        });
    }
};
