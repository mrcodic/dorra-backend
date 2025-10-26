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
        Schema::table('carousels', function (Blueprint $table) {
            $table->after('subtitle',function (Blueprint $table) {
                $table->string('title_color')->nullable();
                $table->string('subtitle_color')->nullable();

            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carousels', function (Blueprint $table) {
            //
        });
    }
};
