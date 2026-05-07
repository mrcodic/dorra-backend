<?php

use App\Models\DiscountCode;
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
            $table->after('type', function ($table) {
                $table->foreignIdFor(DiscountCode::class)
                    ->nullable()
                    ->constrained()->cascadeOnDelete();
                $table->decimal('discount_amount', 8, 2)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign('discount_code_id');
            $table->dropColumn('discount_code_id');
            $table->dropColumn('discount_amount');
        });
    }
};
