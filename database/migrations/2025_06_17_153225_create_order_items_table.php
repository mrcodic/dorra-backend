<?php

use App\Models\Design;
use App\Models\Order;
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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Design::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('quantity')->nullable();
            $table->decimal('base_price')->nullable();
            $table->decimal('custom_product_price')->nullable();
            $table->decimal('total_price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
