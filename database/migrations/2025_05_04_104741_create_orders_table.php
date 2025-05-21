<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignIdFor(User::class)->constrained()->nullOnDelete();
            $table->string('delivery_method');
            $table->string('payment_method');
            $table->tinyInteger('payment_status')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount');
            $table->decimal('delivery_amount');
            $table->decimal('tax_amount');
            $table->decimal('total_price', 10, 2);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
