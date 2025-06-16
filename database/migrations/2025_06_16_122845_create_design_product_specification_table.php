<?php

use App\Models\{Design,
    ProductSpecification,
    ProductSpecificationOption
};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('design_product_specification', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Design::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ProductSpecification::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ProductSpecificationOption::class, 'spec_option_id')
                ->constrained('product_specification_options')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_product_specification');
    }
};
