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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method')->nullable()->change();
            $table->string('payment_method')->nullable()->change();
            $table->tinyInteger('payment_status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method')->nullable(false)->change();
            $table->string('payment_method')->nullable(false)->change();
            $table->tinyInteger('payment_status')->nullable(false)->change();
        });
    }
};
