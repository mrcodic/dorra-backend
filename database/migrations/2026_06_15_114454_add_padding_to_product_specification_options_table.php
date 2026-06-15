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
        Schema::table('product_specification_options', function (Blueprint $table) {
            $table->unsignedInteger('padding')
                ->nullable()
                ->after('fixed_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_specification_options', function (Blueprint $table) {
            $table->dropColumn('padding');
        });
    }
};
