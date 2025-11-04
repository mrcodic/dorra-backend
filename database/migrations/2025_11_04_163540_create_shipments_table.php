<?php

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
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();

            $t->string('provider');
            $t->string('provider_order_id');
            $t->string('tracking_number')->nullable();
            $t->string('status')->nullable();
            $t->json('meta')->nullable();
            $t->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $t->timestamps();

            $t->unique(['provider','provider_order_id']);
            $t->index('tracking_number');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
