<?php

use App\Models\CartItem;

use App\Models\{Order, ProductSpecification, ProductSpecificationOption};
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
        Schema::create('order_item_specs', function (Blueprint $table) {
            $table->id();
            $table->string('spec_name');
            $table->string('option_name');
            $table->string('option_price');
            $table->foreignIdFor(\App\Models\OrderItem::class, 'order_item_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_specs');

    }
};
