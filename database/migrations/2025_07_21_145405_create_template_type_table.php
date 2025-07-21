<?php

use App\Models\Type;
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
        Schema::create('template_type', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('template_id')->constrained('templates')->cascadeOnDelete();
            $table->foreignIdFor(Type::class)->constrained('types')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_type');
    }
};
