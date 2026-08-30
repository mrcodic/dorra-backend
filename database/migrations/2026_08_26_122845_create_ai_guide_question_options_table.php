<?php

use App\Models\AiGuideQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_guide_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AiGuideQuestion::class)->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->json('label');
            $table->json('prompt_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['ai_guide_question_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_guide_question_options');
    }
};
