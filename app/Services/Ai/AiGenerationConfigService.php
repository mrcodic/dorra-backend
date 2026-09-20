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
    public function getAssignedQuestions(
        int $aiCategoryId,
        ?int $aiStudioItemId = null
    ): Collection {
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
            $studioItem = $aiCategory->studioItems
                ->firstWhere('id', $aiStudioItemId);

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

        /*
         * Question assignments from:
         * - AI Product
         * - selected Studio Item
         */
        $questionAssignments = $this
            ->contextAssignments(
                'ai_guide_question_assignments',
                $contexts
            )
            ->filter(
                fn($row) =>
                    $row->is_active === null
                    || (bool) $row->is_active
            )
            ->values();

        if ($questionAssignments->isEmpty()) {
            return collect();
        }

        /*
         * Load conditional rules by assignment ID.
         */
        $conditionsByAssignmentId = collect();

        if (Schema::hasTable('ai_guide_question_conditions')) {
            $conditionsByAssignmentId = DB::table(
                'ai_guide_question_conditions'
            )
                ->whereIn(
                    'ai_guide_question_assignment_id',
                    $questionAssignments->pluck('id')
                )
                ->get()
                ->keyBy(
                    fn($row) =>
                    (int) $row->ai_guide_question_assignment_id
                );
        }

        $questionIds = $questionAssignments
            ->pluck('ai_guide_question_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        /*
         * Load questions with ALL active options first.
         *
         * We need the full option collection to resolve:
         *
         * parent question ID
         *      ↓
         * question key
         *
         * parent option ID
         *      ↓
         * option value
         */
        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->with([
                'options' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->get()
            ->keyBy(
                fn($question) => (int) $question->id
            );

        /*
         * Keep lookup maps BEFORE filtering options.
         */
        $questionKeyById = $questions
            ->mapWithKeys(
                fn($question) => [
                    (int) $question->id => $question->key,
                ]
            );

        $optionById = $questions
            ->flatMap(fn($question) => $question->options)
            ->keyBy(
                fn($option) => (int) $option->id
            );

        /*
         * Option assignments.
         */
        $optionAssignments = $this
            ->contextAssignments(
                'ai_guide_option_assignments',
                $contexts
            )
            ->filter(
                fn($row) =>
                    $row->is_active === null
                    || (bool) $row->is_active
            )
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
                $questionKeyById,
                $optionById
            ) {
                $questionId = (int) $questionId;

                $question = $questions->get($questionId);

                if (!$question) {
                    return null;
                }

                $assignments = $questionAssignments
                    ->filter(
                        fn($assignment) =>
                            (int) $assignment->ai_guide_question_id
                            === $questionId
                    )
                    ->values();

                /*
                 * Resolve required.
                 *
                 * Any assignment required=true wins.
                 *
                 * Otherwise:
                 * explicit false wins over global.
                 */
                $requiredValues = $assignments
                    ->pluck('required')
                    ->filter(
                        fn($value) => $value !== null
                    );

                if (
                    $requiredValues->contains(
                        fn($value) => (bool) $value
                    )
                ) {
                    $question->setAttribute(
                        'resolved_required',
                        true
                    );
                } elseif ($requiredValues->isNotEmpty()) {
                    $question->setAttribute(
                        'resolved_required',
                        false
                    );
                } else {
                    $question->setAttribute(
                        'resolved_required',
                        (bool) $question->required
                    );
                }

                /*
                 * Resolve sort order.
                 */
                $resolvedSortOrder = $assignments
                    ->pluck('sort_order')
                    ->filter(
                        fn($value) => $value !== null
                    )
                    ->map(fn($value) => (int) $value)
                    ->min();

                $question->setAttribute(
                    'resolved_sort_order',
                    $resolvedSortOrder
                    ?? (int) ($question->sort_order ?? 0)
                );

                /*
                 * Resolve options.
                 */
                $allOptions = $question->options
                    ->filter(
                        fn($option) =>
                        (bool) ($option->is_active ?? true)
                    )
                    ->sortBy([
                        ['sort_order', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();

                $useAllOptions = $assignments->contains(
                    fn($assignment) =>
                        ($assignment->options_mode ?? null)
                        === 'all'
                );

                $resolvedOptions = $useAllOptions
                    ? $allOptions
                    : $allOptions
                        ->whereIn(
                            'id',
                            $assignedOptionIds
                        )
                        ->values();

                $question->setAttribute(
                    'assigned_option_ids',
                    $resolvedOptions
                        ->pluck('id')
                        ->map(fn($id) => (int) $id)
                        ->all()
                );

                $question->setRelation(
                    'options',
                    $resolvedOptions
                );

                /*
                 * =====================================================
                 * CONDITION
                 * =====================================================
                 *
                 * Important merge rule:
                 *
                 * If Product OR Studio Item assigns this question
                 * without a condition:
                 *
                 *      show_when = null
                 *
                 * because that question is unconditional.
                 */
                $assignmentConditions = $assignments
                    ->map(
                        fn($assignment) =>
                        $conditionsByAssignmentId->get(
                            (int) $assignment->id
                        )
                    );

                $hasUnconditionalAssignment =
                    $assignmentConditions->contains(
                        fn($condition) =>
                            $condition === null
                    );

                $question->setAttribute(
                    'show_when',
                    null
                );

                if (!$hasUnconditionalAssignment) {
                    /*
                     * For the simple frontend contract,
                     * use the first unique condition.
                     */
                    $condition = $assignmentConditions
                        ->filter()
                        ->unique(
                            fn($condition) => implode(':', [
                                (int) $condition->parent_question_id,
                                (int) $condition->parent_option_id,
                                $condition->operator ?: 'selected',
                            ])
                        )
                        ->first();

                    if ($condition) {
                        $parentQuestionId =
                            (int) $condition->parent_question_id;

                        $parentOptionId =
                            (int) $condition->parent_option_id;

                        $parentQuestionKey =
                            $questionKeyById->get(
                                $parentQuestionId
                            );

                        $parentOption =
                            $optionById->get(
                                $parentOptionId
                            );

                        /*
                         * Extra safety:
                         * make sure this option actually belongs
                         * to the parent question.
                         */
                        if (
                            $parentQuestionKey
                            && $parentOption
                            && (int) $parentOption->ai_guide_question_id
                            === $parentQuestionId
                        ) {
                            $question->setAttribute(
                                'show_when',
                                [
                                    'questionId' =>
                                        $parentQuestionKey,

                                    'optionValue' =>
                                        $parentOption->value,

                                    'operator' =>
                                        $condition->operator
                                            ?: 'selected',
                                ]
                            );
                        }
                    }
                }

                return $question;
            })
            ->filter()
            ->sortBy(
                fn($question) => [
                    (int) (
                        $question->resolved_sort_order
                        ?? 0
                    ),
                    (int) $question->id,
                ]
            )
            ->values();
    }

    private function contextAssignments(
        string $table,
        array $contexts
    ): Collection {
        return DB::table($table)
            ->where(function ($query) use ($contexts) {
                foreach ($contexts as $context) {
                    $query->orWhere(
                        function ($subQuery) use ($context) {
                            $subQuery
                                ->where(
                                    'assignable_id',
                                    $context['id']
                                )
                                ->whereIn(
                                    'assignable_type',
                                    $context['types']
                                );
                        }
                    );
                }
            })
            ->get();
    }
}
