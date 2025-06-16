<?php

use App\Models\Design;
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
        Schema::create('design_product_price', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Design::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ProductPrice::class)->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_product_price');
    }
};
