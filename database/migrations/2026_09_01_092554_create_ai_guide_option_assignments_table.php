<?php

use App\Models\AiGuideQuestionOption;
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
        Schema::create('ai_guide_option_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(AiGuideQuestionOption::class)->constrained()
                ->cascadeOnDelete();

            $table->morphs('assignable');

            $table->json('prompt_value_override')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique([
                'ai_guide_question_option_id',
                'assignable_type',
                'assignable_id'
            ], 'ai_option_assignment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_guide_option_assignments');
    }
};
