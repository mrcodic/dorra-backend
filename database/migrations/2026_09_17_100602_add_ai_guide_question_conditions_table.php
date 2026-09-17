<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_guide_question_conditions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ai_guide_question_assignment_id')
                ->constrained('ai_guide_question_assignments')
                ->cascadeOnDelete();

            $table->foreignId('parent_question_id')
                ->constrained('ai_guide_questions')
                ->cascadeOnDelete();

            $table->foreignId('parent_option_id')
                ->constrained('ai_guide_question_options')
                ->cascadeOnDelete();

            $table->string('operator', 30)->default('selected');

            $table->timestamps();

            $table->unique(
                'ai_guide_question_assignment_id',
                'ai_guide_question_conditions_assignment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guide_question_conditions');
    }
};
