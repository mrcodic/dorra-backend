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
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['design_id']);
            $table->dropColumn('design_id');
            $table->after('id',function ($table){
                $table->string('itemable_type');
                $table->string('itemable_id');
                $table->index(['itemable_type', 'itemable_id'], 'itemable_index');

                $table->foreignIdFor(\App\Models\Product::class)->nullable()->constrained()->nullOnDelete();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'itemable_id', 'itemable_type']);
            $table->foreignIdFor(\App\Models\Design::class)->constrained()->cascadeOnDelete();
        });
    }
};
