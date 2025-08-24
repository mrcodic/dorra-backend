<?php

use App\Models\Design;
use App\Models\Product;
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
                $table->unsignedBigInteger('quantity')->nullable();
                $table->foreignIdFor(Product::class)->nullable()->constrained()->cascadeOnDelete();
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
            $table->foreignIdFor(Design::class)->constrained()->cascadeOnDelete();
        });
    }
};
