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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');


            $table->string('currency', 3)->default('EGP');
            $table->decimal('amount', 10, 2);

            $table->string('payment_method');
            $table->string('payment_status');

            $table->string('transaction_id')->unique();
            $table->string('wallet_reference')->nullable();
            $table->string('kiosk_reference')->nullable();

            $table->timestamp('expiration_date')->nullable();
            $table->text('success_url')->nullable();
            $table->text('failure_url')->nullable();
            $table->text('pending_url')->nullable();

            $table->text('response_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
