<?php

use App\Models\ProductSpecification;
use App\Models\ProductSpecificationOption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('customizable', function (Blueprint $table) {
            $table->id();
            $table->string('customizable_type');
            $table->string('customizable_id');
            $table->index(['customizable_type', 'customizable_id'], 'customizable_index');
            $table->nullableMorphs('owner');
            $table->foreignIdFor(ProductSpecification::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ProductSpecificationOption::class, 'spec_option_id')
                ->constrained('product_specification_options')
                ->cascadeOnDelete();
            $table->decimal('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customizable');
    }
};

