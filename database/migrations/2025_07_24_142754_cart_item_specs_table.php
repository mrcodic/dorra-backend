<?php

use App\Models\CartItem;

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
        Schema::create('cart_item_specs', function (Blueprint $table) {
                $table->string('spec_name');
                $table->string('option_name');
                $table->string('option_price');
            $table->foreignIdFor(CartItem::class, 'cart_item_id')->constrained()->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_item_specs');

    }
};
