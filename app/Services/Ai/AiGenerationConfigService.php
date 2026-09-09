<?php

namespace App\Services\Ai;

use App\Models\AiCategory;
use Illuminate\Support\Collection;

class AiGenerationConfigService
{
    /**
     * Guided questions now belong only to the AI Product (AiCategory).
     * The second argument remains optional temporarily so old callers do not break.
     */
    public function getAssignedQuestions(int $aiCategoryId, ?int $unusedStudioItemId = null): Collection
    {
        $aiCategory = AiCategory::query()
            ->with([
                'questions' => fn($query) => $query
                    ->where('ai_guide_questions.is_active', true)
                    ->with('options'),
                'options',
            ])
            ->findOrFail($aiCategoryId);

        $assignedOptions = $aiCategory->options
            ->filter(fn($option) => (bool) ($option->pivot->is_active ?? true));

        $assignedOptionIdsByQuestion = $assignedOptions
            ->groupBy('ai_guide_question_id')
            ->map(fn($options) => $options->pluck('id')->map(fn($id) => (int) $id)->values());

        return $aiCategory->questions
            ->filter(fn($question) => (bool) ($question->pivot->is_active ?? true))
            ->sortBy(fn($question) => $question->pivot->sort_order ?? $question->sort_order ?? 0)
            ->values()
            ->map(function ($question) use ($assignedOptionIdsByQuestion) {
                $requiredOverride = $question->pivot->required;

                $question->resolved_required = $requiredOverride === null
                    ? (bool) $question->required
                    : (bool) $requiredOverride;

                $assignedOptionIds = $assignedOptionIdsByQuestion
                    ->get($question->id, collect())
                    ->values();

                $question->assigned_option_ids = $assignedOptionIds->all();

                $question->setRelation(
                    'options',
                    $question->options
                        ->whereIn('id', $assignedOptionIds)
                        ->sortBy('sort_order')
                        ->values()
                );

                return $question;
            });
    }
}
