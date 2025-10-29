<?php

use App\Models\Industry;
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
        Schema::create('industryables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Industry::class)->constrained()->cascadeOnDelete();
            $table->string('industryable_id');
            $table->string('industryable_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('industryables');
    }
};
