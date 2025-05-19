<?php

use App\Models\DiscountCode;
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
        Schema::create('discountables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DiscountCode::class)->constrained()->cascadeOnDelete();
            $table->morphs('discountable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discountables');
    }
};
