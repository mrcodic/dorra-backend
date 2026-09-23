<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AiStudioItemQuestionConfigController extends Controller
{
    public function update(
        Request $request,
        AiStudioItem $studioItem
    ): JsonResponse {
        abort_unless(
            auth()->user()?->hasPermissionTo(
                'ai-studio-items_update'
            ),
            403
        );

        $validated = $request->validate([
            'questions' => [
                'present',
                'array',
            ],

            'questions.*' => [
                'array',
            ],

            'questions.*.question_id' => [
                'required',
                'integer',
                'exists:ai_guide_questions,id',
            ],

            'questions.*.required' => [
                'nullable',
                'boolean',
            ],

            'questions.*.sort_order' => [
                'required',
                'integer',
                'min:0',
            ],

            'questions.*.conditions' => [
                'nullable',
                'array',
            ],

            'questions.*.conditions.*.parent_question_id' => [
                'required',
                'integer',
                'exists:ai_guide_questions,id',
            ],

            'questions.*.conditions.*.parent_option_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'questions.*.conditions.*.parent_option_ids.*' => [
                'integer',
                'exists:ai_guide_question_options,id',
            ],

            'questions.*.conditions.*.operator' => [
                'nullable',
                Rule::in([
                    'selected',
                    'not_selected',
                ]),
            ],
        ]);

        $rows = collect(
            $validated['questions'] ?? []
        )
            ->map(function ($row) {
                return [
                    'question_id' =>
                        (int) $row['question_id'],

                    'required' =>
                        (bool) (
                            $row['required']
                            ?? false
                        ),

                    'sort_order' =>
                        (int) (
                            $row['sort_order']
                            ?? 0
                        ),

                    'conditions' =>
                        collect(
                            $row['conditions']
                            ?? []
                        )
                            ->values()
                            ->all(),
                ];
            })
            ->unique('question_id')
            ->values();

        $questionIds = $rows
            ->pluck('question_id')
            ->values();

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn(
                'id',
                $questionIds
            )
            ->with([
                'options' => fn ($query) =>
                $query->where(
                    'is_active',
                    true
                ),
            ])
            ->get()
            ->keyBy(
                fn ($question) =>
                (int) $question->id
            );

        if (
            $questions->count()
            !== $questionIds->count()
        ) {
            throw ValidationException::withMessages([
                'questions' => [
                    'One or more selected questions are unavailable.',
                ],
            ]);
        }

        $selectedRows = $rows->keyBy(
            'question_id'
        );

        /*
         * Validate all conditional rules before deleting the current config.
         */
        foreach ($rows as $rowIndex => $row) {
            $childQuestionId =
                (int) $row['question_id'];

            $childSort =
                (int) $row['sort_order'];

            foreach (
                $row['conditions']
                as $conditionIndex => $condition
            ) {
                $parentQuestionId =
                    (int) (
                        $condition[
                        'parent_question_id'
                        ]
                        ?? 0
                    );

                if (
                    !$selectedRows->has(
                        $parentQuestionId
                    )
                ) {
                    throw ValidationException::withMessages([
                        "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                            'The parent question must also be attached to this Studio Item.',
                        ],
                    ]);
                }

                if (
                    $parentQuestionId
                    === $childQuestionId
                ) {
                    throw ValidationException::withMessages([
                        "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                            'A question cannot depend on itself.',
                        ],
                    ]);
                }

                $parentQuestion =
                    $questions->get(
                        $parentQuestionId
                    );

                if (
                    !$parentQuestion
                    || !in_array(
                        $parentQuestion->type?->value
                        ?? $parentQuestion->type,
                        [
                            'single_select',
                            'multi_select',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                            'The parent question must be a selectable question.',
                        ],
                    ]);
                }

                $parentSort =
                    (int) (
                        $selectedRows
                            ->get(
                                $parentQuestionId
                            )['sort_order']
                        ?? 0
                    );

                if (
                    $parentSort
                    >= $childSort
                ) {
                    throw ValidationException::withMessages([
                        "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                            'The parent question must have a lower order than the child question.',
                        ],
                    ]);
                }

                $validOptionIds =
                    $parentQuestion
                        ->options
                        ->pluck('id')
                        ->map(
                            fn ($id) =>
                            (int) $id
                        );

                $submittedOptionIds =
                    collect(
                        $condition[
                        'parent_option_ids'
                        ]
                        ?? []
                    )
                        ->map(
                            fn ($id) =>
                            (int) $id
                        )
                        ->filter()
                        ->unique()
                        ->values();

                if (
                    $submittedOptionIds
                        ->isEmpty()
                    || $submittedOptionIds
                        ->diff(
                            $validOptionIds
                        )
                        ->isNotEmpty()
                ) {
                    throw ValidationException::withMessages([
                        "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_option_ids" => [
                            'One or more selected answers do not belong to the parent question.',
                        ],
                    ]);
                }
            }
        }

        DB::transaction(function () use (
            $studioItem,
            $rows,
            $questions
        ) {
            $morphTypes =
                $this->studioItemMorphTypes(
                    $studioItem
                );

            $morphType =
                $studioItem->getMorphClass();

            $oldAssignments =
                DB::table(
                    'ai_guide_question_assignments'
                )
                    ->where(
                        'assignable_id',
                        $studioItem->id
                    )
                    ->whereIn(
                        'assignable_type',
                        $morphTypes
                    )
                    ->get();

            if (
                $oldAssignments->isNotEmpty()
                && DB::getSchemaBuilder()
                    ->hasTable(
                        'ai_guide_question_conditions'
                    )
            ) {
                DB::table(
                    'ai_guide_question_conditions'
                )
                    ->whereIn(
                        'ai_guide_question_assignment_id',
                        $oldAssignments
                            ->pluck('id')
                    )
                    ->delete();
            }

            DB::table(
                'ai_guide_option_assignments'
            )
                ->where(
                    'assignable_id',
                    $studioItem->id
                )
                ->whereIn(
                    'assignable_type',
                    $morphTypes
                )
                ->delete();

            DB::table(
                'ai_guide_question_assignments'
            )
                ->where(
                    'assignable_id',
                    $studioItem->id
                )
                ->whereIn(
                    'assignable_type',
                    $morphTypes
                )
                ->delete();

            if ($rows->isEmpty()) {
                return;
            }

            $now = now();
            $assignmentIdByQuestionId =
                collect();

            foreach ($rows as $row) {
                $assignmentId =
                    DB::table(
                        'ai_guide_question_assignments'
                    )
                        ->insertGetId([
                            'ai_guide_question_id' =>
                                (int) $row[
                                'question_id'
                                ],

                            'assignable_type' =>
                                $morphType,

                            'assignable_id' =>
                                $studioItem->id,

                            'required' =>
                                (bool) $row[
                                'required'
                                ],

                            'is_active' =>
                                true,

                            'sort_order' =>
                                (int) $row[
                                'sort_order'
                                ],

                            'options_mode' =>
                                'all',

                            'created_at' =>
                                $now,

                            'updated_at' =>
                                $now,
                        ]);

                $assignmentIdByQuestionId
                    ->put(
                        (int) $row[
                        'question_id'
                        ],
                        (int) $assignmentId
                    );
            }

            $optionRows = [];

            foreach ($rows as $row) {
                $question =
                    $questions->get(
                        (int) $row[
                        'question_id'
                        ]
                    );

                if (
                    !$question
                    || !in_array(
                        $question->type?->value
                        ?? $question->type,
                        [
                            'single_select',
                            'multi_select',
                        ],
                        true
                    )
                ) {
                    continue;
                }

                foreach (
                    $question
                        ->options
                        ->values()
                    as $optionSort => $option
                ) {
                    $optionRows[] = [
                        'ai_guide_question_option_id' =>
                            (int) $option->id,

                        'assignable_type' =>
                            $morphType,

                        'assignable_id' =>
                            $studioItem->id,

                        'prompt_value_override' =>
                            null,

                        'is_active' =>
                            true,

                        'sort_order' =>
                            (int) $optionSort,

                        'created_at' =>
                            $now,

                        'updated_at' =>
                            $now,
                    ];
                }
            }

            if ($optionRows) {
                DB::table(
                    'ai_guide_option_assignments'
                )->insert(
                    $optionRows
                );
            }

            $conditionRows = [];

            foreach ($rows as $row) {
                $assignmentId =
                    $assignmentIdByQuestionId
                        ->get(
                            (int) $row[
                            'question_id'
                            ]
                        );

                foreach (
                    $row['conditions']
                    as $condition
                ) {
                    $parentQuestionId =
                        (int) $condition[
                        'parent_question_id'
                        ];

                    $operator =
                        (string) (
                            $condition[
                            'operator'
                            ]
                            ?? 'selected'
                        );

                    $parentOptionIds =
                        collect(
                            $condition[
                            'parent_option_ids'
                            ]
                            ?? []
                        )
                            ->map(
                                fn ($id) =>
                                (int) $id
                            )
                            ->filter()
                            ->unique()
                            ->values();

                    foreach (
                        $parentOptionIds
                        as $parentOptionId
                    ) {
                        $conditionRows[] = [
                            'ai_guide_question_assignment_id' =>
                                (int) $assignmentId,

                            'parent_question_id' =>
                                $parentQuestionId,

                            'parent_option_id' =>
                                (int) $parentOptionId,

                            'operator' =>
                                $operator,

                            'created_at' =>
                                $now,

                            'updated_at' =>
                                $now,
                        ];
                    }
                }
            }

            if ($conditionRows) {
                DB::table(
                    'ai_guide_question_conditions'
                )->insert(
                    $conditionRows
                );
            }
        });

        return response()->json([
            'message' =>
                'Studio Item questions saved successfully.',

            'data' => [
                'studio_item_id' =>
                    (int) $studioItem->id,

                'question_count' =>
                    $rows->count(),
            ],
        ]);
    }

    private function studioItemMorphTypes(
        AiStudioItem $studioItem
    ): array {
        return array_values(
            array_unique([
                $studioItem->getMorphClass(),
                'ai_studio_item',
                AiStudioItem::class,
            ])
        );
    }
}
