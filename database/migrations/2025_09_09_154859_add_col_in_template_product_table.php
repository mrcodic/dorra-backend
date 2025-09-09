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
        Schema::table('template_product', function (Blueprint $table) {
            if (Schema::hasColumn('product_prices', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            $table->after('template_id', function ($table) {
                $table->morphs('referenceable');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_product', function (Blueprint $table) {
            $table->dropIndex('referenceable_index');
            $table->dropColumn(['referenceable_id', 'referenceable_type']);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
