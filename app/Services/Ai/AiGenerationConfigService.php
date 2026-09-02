<?php

namespace App\Services\Ai;

use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AiGenerationConfigService
{
    public function getAssignedQuestions(
        int $aiCategoryId,
        int $aiStudioItemId
    ): Collection {
        $contexts = [
            [
                'type' => (new AiCategory())->getMorphClass(),
                'id' => $aiCategoryId,
            ],
            [
                'type' => (new AiStudioItem())->getMorphClass(),
                'id' => $aiStudioItemId,
            ],
        ];

        $assignments = DB::table('ai_guide_question_assignments')
            ->where(function ($query) use ($contexts) {
                foreach ($contexts as $context) {
                    $query->orWhere(function ($q) use ($context) {
                        $q->where('assignable_type', $context['type'])
                            ->where('assignable_id', $context['id']);
                    });
                }
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $questionIds = $assignments
            ->pluck('ai_guide_question_id')
            ->unique()
            ->values();

        if ($questionIds->isEmpty()) {
            return collect();
        }

        $questions = AiGuideQuestion::query()
            ->whereIn('id', $questionIds)
            ->where('is_active', true)
            ->with('options')
            ->get()
            ->keyBy('id');

        $optionAssignments = DB::table('ai_guide_option_assignments')
            ->where(function ($query) use ($contexts) {
                foreach ($contexts as $context) {
                    $query->orWhere(function ($q) use ($context) {
                        $q->where('assignable_type', $context['type'])
                            ->where('assignable_id', $context['id']);
                    });
                }
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $assignedOptionIds = $optionAssignments
            ->pluck('ai_guide_question_option_id')
            ->map(fn($id) => (int) $id)
            ->unique();

        return $questionIds
            ->map(function ($questionId) use (
                $questions,
                $assignments,
                $assignedOptionIds
            ) {
                $question = $questions->get($questionId);

                if (!$question) {
                    return null;
                }

                $questionAssignments = $assignments
                    ->where(
                        'ai_guide_question_id',
                        $questionId
                    );

                $question->setAttribute(
                    'resolved_required',
                    $questionAssignments->contains(
                        fn($assignment) =>
                        (bool) $assignment->required
                    )
                );

                $questionOptionIds = $question
                    ->options
                    ->pluck('id')
                    ->map(fn($id) => (int) $id);

                $question->setAttribute(
                    'assigned_option_ids',
                    $assignedOptionIds
                        ->intersect($questionOptionIds)
                        ->values()
                        ->all()
                );

                return $question;
            })
            ->filter()
            ->values();
    }
}
