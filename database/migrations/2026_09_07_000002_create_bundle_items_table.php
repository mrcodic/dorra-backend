<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bundle_id')
                ->constrained('bundles')
                ->cascadeOnDelete();

            $table->morphs('itemable');

            $table->string('role');

            $table->string('quantity_rule')->nullable();


            $table->unsignedInteger('quantity')->default(1);


            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 8, 2)->nullable();

            /*
             * Optional safety cap for rewards with variable configurations.
             * Example: 100% free with max_discount_amount=200 means:
             * selected option 150 => pays 0
             * selected option 250 => pays 50
             */
            $table->decimal('max_discount_amount', 12, 2)->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['bundle_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');
    }
};
