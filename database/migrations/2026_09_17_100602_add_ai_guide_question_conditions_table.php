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

            $table->unsignedBigInteger('ai_guide_question_assignment_id');
            $table->unsignedBigInteger('parent_question_id');
            $table->unsignedBigInteger('parent_option_id');

            $table->string('operator', 30)->default('selected');

            $table->timestamps();

            $table->unique(
                'ai_guide_question_assignment_id',
                'agqc_assignment_unique'
            );

            $table->foreign(
                'ai_guide_question_assignment_id',
                'agqc_assignment_fk'
            )
                ->references('id')
                ->on('ai_guide_question_assignments')
                ->cascadeOnDelete();

            $table->foreign(
                'parent_question_id',
                'agqc_question_fk'
            )
                ->references('id')
                ->on('ai_guide_questions')
                ->cascadeOnDelete();

            $table->foreign(
                'parent_option_id',
                'agqc_option_fk'
            )
                ->references('id')
                ->on('ai_guide_question_options')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guide_question_conditions');
    }
};
