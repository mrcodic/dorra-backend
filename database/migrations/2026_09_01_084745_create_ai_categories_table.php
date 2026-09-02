<?php

use App\Models\Category;
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
        Schema::create('ai_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Category::class)->constrained()
                ->cascadeOnDelete();

            $table->boolean('enabled')->default(true);

            $table->string('default_resolution')->nullable();
            $table->string('aspect_ratio')->nullable();

            $table->unsignedInteger('credits_cost')->default(1);

            $table->string('provider')->nullable();
            $table->string('model')->nullable();

            $table->json('settings')->nullable();

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_categories');
    }
};
