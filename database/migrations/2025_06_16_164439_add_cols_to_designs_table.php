<?php

use App\Models\ProductPrice;
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
        Schema::table('designs', function (Blueprint $table) {
            $table->after('current_version',function (Blueprint $table) {
                $table->foreignIdFor(ProductPrice::class)->nullable()->constrained()->cascadeOnDelete();
            });

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('designs', function (Blueprint $table) {
            $table->dropForeign(['product_price_id']);
            $table->dropColumn('product_price_id');
        });
    }
};
