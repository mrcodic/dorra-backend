<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dimension_product', function (Blueprint $table) {
            if (Schema::hasColumn('dimension_product', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            $table->after('dimension_id', function (Blueprint $table) {
                $table->morphs('dimensionable');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimension_product', function (Blueprint $table) {
            $table->dropIndex('dimensionable_index');
            $table->dropColumn(['dimensionable_id', 'dimensionable_type']);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
