<?php

use App\Models\AiGuideQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_guide_question_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(AiGuideQuestion::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->string('assignable_type');
            
            $table->unsignedBigInteger('assignable_id');
            $table->boolean('required')->nullable();
            $table->boolean('is_active')->nullable();
            $table->unsignedInteger('sort_order')->nullable();
            $table->string('options_mode', 20)->nullable();

            $table->timestamps();

            $table->index(
                ['assignable_type', 'assignable_id'],
                'ai_question_assignable_index'
            );

            $table->unique([
                'ai_guide_question_id',
                'assignable_type',
                'assignable_id'
            ], 'ai_question_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guide_question_assignments');
    }
};
