<?php

namespace App\Services\Ai;

use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AiGenerationConfigService
{
    public function getAssignedQuestions(int $aiCategoryId, ?int $aiStudioItemId = null): Collection
    {
        $aiCategory = AiCategory::query()
            ->with('studioItems')
            ->findOrFail($aiCategoryId);

        $contexts = [[
            'id' => $aiCategory->id,
            'types' => array_values(array_unique([
                $aiCategory->getMorphClass(),
                'ai_category',
                AiCategory::class,
            ])),
        ]];

        if ($aiStudioItemId !== null) {
            $studioItem = $aiCategory->studioItems->firstWhere('id', $aiStudioItemId);

            if (!$studioItem || !$studioItem->is_active) {
                throw ValidationException::withMessages([
                    'ai_studio_item_id' => [
                        'The selected AI Studio Item is not enabled for this AI Product.',
                    ],
                ]);
            }

            $contexts[] = [
                'id' => $studioItem->id,
                'types' => array_values(array_unique([
                    $studioItem->getMorphClass(),
                    'ai_studio_item',
                    AiStudioItem::class,
                ])),
            ];
        }

        $questionAssignments = $this
            ->contextAssignments('ai_guide_question_assignments', $contexts)
            ->filter(fn($row) => $row->is_active === null || (bool) $row->is_active)
            ->values();

        if ($questionAssignments->isEmpty()) {
            return collect();
        }

        /*
         * IMPORTANT:
         * One assignment can now have MANY condition rows.
         */
        $conditionsByAssignmentId = collect();

        if (Schema::hasTable('ai_guide_question_conditions')) {
            $conditionsByAssignmentId = DB::table('ai_guide_question_conditions')
                ->whereIn(
                    'ai_guide_question_assignment_id',
                    $questionAssignments->pluck('id')
                )
                ->orderBy('id')
                ->get()
                ->groupBy(
                    fn($row) => (int) $row->ai_guide_question_assignment_id
                );
        }

        $questionIds = $questionAssignments
            ->pluck('ai_guide_question_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->with([
                'options' => fn($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->get()
            ->keyBy(fn($question) => (int) $question->id);

        /*
         * Keep a full option lookup BEFORE options are filtered per assignment.
         * It is used to convert condition option IDs to frontend option values.
         */
        $allOptionsById = $questions
            ->flatMap(fn($question) => $question->options)
            ->keyBy(fn($option) => (int) $option->id);

        $optionAssignments = $this
            ->contextAssignments('ai_guide_option_assignments', $contexts)
            ->filter(fn($row) => $row->is_active === null || (bool) $row->is_active)
            ->values();

        $assignedOptionIds = $optionAssignments
            ->pluck('ai_guide_question_option_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        return $questionIds
            ->map(function ($questionId) use (
                $questions,
                $questionAssignments,
                $assignedOptionIds,
                $conditionsByAssignmentId,
                $allOptionsById
            ) {
                $questionId = (int) $questionId;
                $question = $questions->get($questionId);

                if (!$question) {
                    return null;
                }

                $assignments = $questionAssignments
                    ->filter(
                        fn($assignment) =>
                            (int) $assignment->ai_guide_question_id === $questionId
                    )
                    ->values();

                $requiredValues = $assignments
                    ->pluck('required')
                    ->filter(fn($value) => $value !== null);

                if ($requiredValues->contains(fn($value) => (bool) $value)) {
                    $question->setAttribute('resolved_required', true);
                } elseif ($requiredValues->isNotEmpty()) {
                    $question->setAttribute('resolved_required', false);
                } else {
                    $question->setAttribute(
                        'resolved_required',
                        (bool) $question->required
                    );
                }

                $question->setAttribute(
                    'resolved_sort_order',
                    $assignments
                        ->pluck('sort_order')
                        ->filter(fn($value) => $value !== null)
                        ->map(fn($value) => (int) $value)
                        ->min() ?? (int) ($question->sort_order ?? 0)
                );

                $allOptions = $question->options
                    ->filter(fn($option) => (bool) ($option->is_active ?? true))
                    ->sortBy('sort_order')
                    ->values();

                $useAllOptions = $assignments->contains(
                    fn($assignment) => ($assignment->options_mode ?? null) === 'all'
                );

                $resolvedOptions = $useAllOptions
                    ? $allOptions
                    : $allOptions
                        ->whereIn('id', $assignedOptionIds)
                        ->values();

                $question->setAttribute(
                    'assigned_option_ids',
                    $resolvedOptions
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->all()
                );

                $question->setRelation('options', $resolvedOptions);

                /*
                 * =========================================================
                 * CONDITIONAL VISIBILITY
                 * =========================================================
                 *
                 * Same parent question + multiple option rows => OR
                 * Different parent questions                  => AND
                 *
                 * If ANY effective assignment has no conditions, the
                 * question remains unconditional. This preserves the old
                 * Product + Studio Item merge behavior.
                 */
                $assignmentConditions = $assignments->mapWithKeys(
                    fn($assignment) => [
                        (int) $assignment->id => collect(
                            $conditionsByAssignmentId->get(
                                (int) $assignment->id,
                                collect()
                            )
                        ),
                    ]
                );

                $question->setAttribute('resolved_conditions', []);
                $question->setAttribute('resolved_condition', null);
                $question->setAttribute('show_when', null);

                $conditionalAssignments = $assignmentConditions
                    ->filter(fn($conditions) => $conditions->isNotEmpty());

                if ($conditionalAssignments->isNotEmpty()) {
                    $rawConditions = $conditionalAssignments
                        ->flatMap(fn($conditions) => $conditions)
                        ->values();

                    /*
                     * Group rows by parent question + operator.
                     * Every option inside a group is OR.
                     */
                    $resolvedConditions = $rawConditions
                        ->groupBy(function ($condition) {
                            return implode(':', [
                                (int) $condition->parent_question_id,
                                (string) ($condition->operator ?: 'selected'),
                            ]);
                        })
                        ->map(function ($conditions) {
                            $first = $conditions->first();

                            return [
                                'parent_question_id' => (int) $first->parent_question_id,
                                'parent_option_ids' => $conditions
                                    ->pluck('parent_option_id')
                                    ->map(fn($id) => (int) $id)
                                    ->unique()
                                    ->values()
                                    ->all(),
                                'operator' => (string) ($first->operator ?: 'selected'),
                            ];
                        })
                        ->values();

                    $question->setAttribute(
                        'resolved_conditions',
                        $resolvedConditions->all()
                    );

                    /*
                     * Keep old internal single condition for compatibility
                     * when there is exactly one parent and one answer.
                     */
                    if (
                        $resolvedConditions->count() === 1
                        && count($resolvedConditions->first()['parent_option_ids']) === 1
                    ) {
                        $single = $resolvedConditions->first();

                        $question->setAttribute('resolved_condition', [
                            'parent_question_id' => $single['parent_question_id'],
                            'parent_option_id' => $single['parent_option_ids'][0],
                            'operator' => $single['operator'],
                        ]);
                    }

                    /*
                     * Keep the API key exactly `showWhen` via the resource.
                     * The attribute below only extends its value so frontend
                     * can support multiple parents and multiple answers.
                     */
                    $showRules = $resolvedConditions
                        ->map(function ($condition) use ($questions, $allOptionsById) {
                            $parentQuestionId = (int) $condition['parent_question_id'];
                            $parentQuestion = $questions->get($parentQuestionId);

                            if (!$parentQuestion) {
                                return null;
                            }

                            $optionValues = collect($condition['parent_option_ids'])
                                ->map(function ($optionId) use ($allOptionsById, $parentQuestionId) {
                                    $option = $allOptionsById->get((int) $optionId);

                                    if (
                                        !$option
                                        || (int) $option->ai_guide_question_id !== $parentQuestionId
                                    ) {
                                        return null;
                                    }

                                    return (string) $option->value;
                                })
                                ->filter(fn($value) => $value !== null && $value !== '')
                                ->unique()
                                ->values();

                            if ($optionValues->isEmpty()) {
                                return null;
                            }

                            return [
                                'questionId' => (string) $parentQuestion->key,
                                'optionValues' => $optionValues->all(),
                                'operator' => $condition['operator'] ?: 'selected',
                            ];
                        })
                        ->filter()
                        ->values();

                    if ($showRules->isNotEmpty()) {
                        $showWhen = [
//                            'logic' => 'all',
                            'rules' => $showRules->all(),
                        ];
                        $question->setAttribute('show_when', $showWhen);
                    }
                }

                return $question;
            })
            ->filter()
            ->sortBy(fn($question) => [
                (int) ($question->resolved_sort_order ?? 0),
                (int) $question->id,
            ])
            ->values();
    }

    private function contextAssignments(string $table, array $contexts): Collection
    {
        return DB::table($table)
            ->where(function ($query) use ($contexts) {
                foreach ($contexts as $context) {
                    $query->orWhere(function ($subQuery) use ($context) {
                        $subQuery
                            ->where('assignable_id', $context['id'])
                            ->whereIn('assignable_type', $context['types']);
                    });
                }
            })
            ->get();
    }
}
