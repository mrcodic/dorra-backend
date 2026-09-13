<?php

namespace App\Services\Ai;

use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiGenerationConfigService
{
    public function getAssignedQuestions(int $aiCategoryId, ?int $aiStudioItemId = null): Collection
    {
        $aiCategory = AiCategory::query()->with('studioItems')->findOrFail($aiCategoryId);
        $contexts = [[
            'id' => $aiCategory->id,
            'types' => array_values(array_unique([$aiCategory->getMorphClass(), 'ai_category', AiCategory::class])),
        ]];

        if ($aiStudioItemId !== null) {
            $studioItem = $aiCategory->studioItems->firstWhere('id', $aiStudioItemId);

            if (!$studioItem || !$studioItem->is_active) {
                throw ValidationException::withMessages([
                    'ai_studio_item_id' => ['The selected AI Studio Item is not enabled for this AI Product.'],
                ]);
            }

            $contexts[] = [
                'id' => $studioItem->id,
                'types' => array_values(array_unique([$studioItem->getMorphClass(), 'ai_studio_item', AiStudioItem::class])),
            ];
        }

        $questionAssignments = $this->contextAssignments('ai_guide_question_assignments', $contexts)
            ->filter(fn($row) => $row->is_active === null || (bool) $row->is_active)
            ->values();

        if ($questionAssignments->isEmpty()) return collect();

        $questionIds = $questionAssignments->pluck('ai_guide_question_id')->map(fn($id) => (int) $id)->unique()->values();

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->with('options')
            ->get()
            ->keyBy('id');

        $optionAssignments = $this->contextAssignments('ai_guide_option_assignments', $contexts)
            ->filter(fn($row) => $row->is_active === null || (bool) $row->is_active)
            ->values();

        $assignedOptionIds = $optionAssignments
            ->pluck('ai_guide_question_option_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        return $questionIds
            ->map(function ($questionId) use ($questions, $questionAssignments, $assignedOptionIds) {
                $question = $questions->get($questionId);
                if (!$question) return null;

                $assignments = $questionAssignments->where('ai_guide_question_id', $questionId)->values();
                $requiredValues = $assignments->pluck('required')->filter(fn($value) => $value !== null);

                if ($requiredValues->contains(fn($value) => (bool) $value)) {
                    $question->resolved_required = true;
                } elseif ($requiredValues->isNotEmpty()) {
                    $question->resolved_required = false;
                } else {
                    $question->resolved_required = (bool) $question->required;
                }

                $question->resolved_sort_order = $assignments
                    ->pluck('sort_order')
                    ->filter(fn($value) => $value !== null)
                    ->map(fn($value) => (int) $value)
                    ->min() ?? (int) ($question->sort_order ?? 0);

                $allOptions = $question->options
                    ->filter(fn($option) => (bool) ($option->is_active ?? true))
                    ->sortBy('sort_order')
                    ->values();

                $useAllOptions = $assignments->contains(fn($assignment) => ($assignment->options_mode ?? null) === 'all');

                $resolvedOptions = $useAllOptions
                    ? $allOptions
                    : $allOptions->whereIn('id', $assignedOptionIds)->values();

                $question->assigned_option_ids = $resolvedOptions->pluck('id')->map(fn($id) => (int) $id)->all();
                $question->setRelation('options', $resolvedOptions);

                return $question;
            })
            ->filter()
            ->sortBy(fn($question) => [$question->resolved_sort_order, $question->id])
            ->values();
    }

    private function contextAssignments(string $table, array $contexts): Collection
    {
        return DB::table($table)
            ->where(function ($query) use ($contexts) {
                foreach ($contexts as $context) {
                    $query->orWhere(function ($subQuery) use ($context) {
                        $subQuery->where('assignable_id', $context['id'])
                            ->whereIn('assignable_type', $context['types']);
                    });
                }
            })
            ->get();
    }
}
