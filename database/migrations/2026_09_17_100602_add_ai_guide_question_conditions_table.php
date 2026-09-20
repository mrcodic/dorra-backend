<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_guide_question_conditions', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('ai_guide_question_assignment_id')
                ->unique('agqc_assignment_unique')
                ->constrained('ai_guide_question_assignments')
                ->cascadeOnDelete()
                ->name('agqc_assignment_fk');

            $table->foreignId('parent_question_id')
                ->constrained('ai_guide_questions')
                ->cascadeOnDelete()
                ->name('agqc_question_fk');

            $table->foreignId('parent_option_id')
                ->constrained('ai_guide_question_options')
                ->cascadeOnDelete()
                ->name('agqc_option_fk');

            $table->string('operator', 30)->default('selected');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guide_question_conditions');
    }
};
