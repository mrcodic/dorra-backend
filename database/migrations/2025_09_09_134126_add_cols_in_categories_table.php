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
        Schema::table('categories', function (Blueprint $table) {
            $table->after('description',function (Blueprint $table) {
                $table->boolean('has_custom_prices')->default(false);
                $table->decimal('base_price')->nullable();
                $table->boolean('has_mockup')->default(false);
                $table->boolean('is_has_category')->default(false);
            });


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('has_custom_prices');
            $table->dropColumn('base_price');
            $table->dropColumn('has_mockup');
//            $table->dropColumn('is_has_category');
        });
    }
};
