<?php

use App\Models\ProductSpecification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specification_template', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('template_id')->constrained('templates')->cascadeOnDelete();
            $table->foreignIdFor(ProductSpecification::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification_template');
    }
};
