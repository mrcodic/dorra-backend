<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_category_studio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_category_id')->constrained('ai_categories')->cascadeOnDelete();
            $table->foreignId('ai_studio_item_id')->constrained('ai_studio_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ai_category_id', 'ai_studio_item_id'], 'ai_category_studio_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_category_studio_items');
    }
};
