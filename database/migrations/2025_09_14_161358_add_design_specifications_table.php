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
        Schema::create('design_specifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('design_id');
            $table->foreign('design_id')->references('design_id')->on('designs')->cascadeOnDelete();

            $table->unsignedBigInteger('product_spec_id');
            $table->foreign('product_spec_id')->references('id')->on('product_specifications')->cascadeOnDelete();

            $table->unsignedBigInteger('option_id')->nullable();
            $table->foreign('option_id')->references('id')->on('product_specification_options')->nullOnDelete();

            $table->timestamps();

            $table->unique(['design_id', 'product_spec_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_specifications');
    }
};
