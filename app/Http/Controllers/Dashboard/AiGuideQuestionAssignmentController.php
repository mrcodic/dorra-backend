<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AiGuideQuestionAssignmentController extends Controller
{
    public function productConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ai_category_id' => ['required', 'integer', 'exists:ai_categories,id'],
        ]);

        $aiCategory = AiCategory::query()->findOrFail(
            (int) $validated['ai_category_id']
        );

        $assignments = $this->categoryAssignments($aiCategory);

        if ($assignments->isEmpty()) {
            return response()->json([
                'data' => [
                    'assignments' => [],
                ],
            ]);
        }

        $conditionCounts = DB::table('ai_guide_question_conditions')
            ->whereIn(
                'ai_guide_question_assignment_id',
                $assignments->pluck('id')
            )
            ->selectRaw(
                'ai_guide_question_assignment_id, COUNT(DISTINCT CONCAT(parent_question_id, ":", operator)) as condition_count'
            )
            ->groupBy('ai_guide_question_assignment_id')
            ->pluck(
                'condition_count',
                'ai_guide_question_assignment_id'
            );

        $payload = $assignments
            ->mapWithKeys(function ($assignment) use ($conditionCounts) {
                $questionId = (int) $assignment->ai_guide_question_id;

                return [
                    $questionId => [
                        'attached' => true,
                        'assignment_id' => (int) $assignment->id,
                        'sort_order' => (int) ($assignment->sort_order ?? 0),
                        'required' => $assignment->required === null
                            ? null
                            : (bool) $assignment->required,
                        'condition_count' => (int) (
                            $conditionCounts->get($assignment->id)
                            ?? 0
                        ),
                    ],
                ];
            })
            ->all();

        return response()->json([
            'data' => [
                'assignments' => $payload,
            ],
        ]);
    }

    public function showConditions(
        Request $request,
        AiGuideQuestion $question
    ): JsonResponse {
        $validated = $request->validate([
            'ai_category_id' => ['required', 'integer', 'exists:ai_categories,id'],
        ]);

        $aiCategory = AiCategory::query()->findOrFail(
            (int) $validated['ai_category_id']
        );

        $assignments = $this->categoryAssignments($aiCategory)
            ->keyBy(fn ($assignment) => (int) $assignment->ai_guide_question_id);

        $assignment = $assignments->get((int) $question->id);

        if (!$assignment) {
            throw ValidationException::withMessages([
                'question' => [
                    'This question is not attached to the selected AI Product.',
                ],
            ]);
        }

        $conditions = DB::table('ai_guide_question_conditions')
            ->where(
                'ai_guide_question_assignment_id',
                $assignment->id
            )
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($condition) => implode(':', [
                (int) $condition->parent_question_id,
                (string) ($condition->operator ?: 'selected'),
            ]))
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'parent_question_id' => (int) $first->parent_question_id,
                    'parent_option_ids' => $rows
                        ->pluck('parent_option_id')
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all(),
                    'operator' => (string) (
                    $first->operator
                        ?: 'selected'
                    ),
                ];
            })
            ->values()
            ->all();

        $parentQuestionIds = $assignments
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->filter(
                fn ($id) => $id !== (int) $question->id
            )
            ->values();

        $parentQuestions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $parentQuestionIds)
            ->with([
                'options' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn ($parent) => in_array(
                $parent->type?->value ?? $parent->type,
                ['single_select', 'multi_select'],
                true
            ))
            ->map(fn ($parent) => [
                'id' => (int) $parent->id,
                'title' => $parent->title,
                'options' => $parent->options
                    ->map(fn ($option) => [
                        'id' => (int) $option->id,
                        'label' => $option->label,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => [
                'question' => [
                    'id' => (int) $question->id,
                    'title' => $question->title,
                ],
                'conditions' => $conditions,
                'parent_questions' => $parentQuestions,
            ],
        ]);
    }

    public function updateConditions(
        Request $request,
        AiGuideQuestion $question
    ): JsonResponse {
        $this->authorizeUpdate();

        $validated = $request->validate([
            'ai_category_id' => [
                'required',
                'integer',
                'exists:ai_categories,id',
            ],
            'conditions' => ['present', 'array'],
            'conditions.*.parent_question_id' => [
                'required',
                'integer',
                'exists:ai_guide_questions,id',
            ],
            'conditions.*.parent_option_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'conditions.*.parent_option_ids.*' => [
                'integer',
                'exists:ai_guide_question_options,id',
            ],
        ]);

        $aiCategory = AiCategory::query()->findOrFail(
            (int) $validated['ai_category_id']
        );

        $assignments = $this->categoryAssignments($aiCategory)
            ->keyBy(fn ($assignment) => (int) $assignment->ai_guide_question_id);

        $assignment = $assignments->get((int) $question->id);

        if (!$assignment) {
            throw ValidationException::withMessages([
                'question' => [
                    'This question is not attached to the selected AI Product.',
                ],
            ]);
        }

        $conditions = collect($validated['conditions'] ?? []);

        $parentQuestionIds = $conditions
            ->pluck('parent_question_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($parentQuestionIds->contains((int) $question->id)) {
            throw ValidationException::withMessages([
                'conditions' => [
                    'A question cannot depend on itself.',
                ],
            ]);
        }

        $missingParentAssignments = $parentQuestionIds
            ->reject(fn ($id) => $assignments->has((int) $id))
            ->values();

        if ($missingParentAssignments->isNotEmpty()) {
            throw ValidationException::withMessages([
                'conditions' => [
                    'Every parent question must be attached to the selected AI Product.',
                ],
            ]);
        }

        $parentQuestions = AiGuideQuestion::query()
            ->whereIn('id', $parentQuestionIds)
            ->with([
                'options' => fn ($query) => $query
                    ->where('is_active', true),
            ])
            ->get()
            ->keyBy('id');

        $insertRows = [];
        $now = now();

        foreach ($conditions as $conditionIndex => $condition) {
            $parentQuestionId = (int) $condition['parent_question_id'];

            $parentQuestion = $parentQuestions->get(
                $parentQuestionId
            );

            if (!$parentQuestion) {
                throw ValidationException::withMessages([
                    "conditions.{$conditionIndex}.parent_question_id" => [
                        'The selected parent question is unavailable.',
                    ],
                ]);
            }

            if (!in_array(
                $parentQuestion->type?->value ?? $parentQuestion->type,
                ['single_select', 'multi_select'],
                true
            )) {
                throw ValidationException::withMessages([
                    "conditions.{$conditionIndex}.parent_question_id" => [
                        'The parent question must support selectable answers.',
                    ],
                ]);
            }

            $validOptionIds = $parentQuestion->options
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $submittedOptionIds = collect(
                $condition['parent_option_ids'] ?? []
            )
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if (
                $submittedOptionIds->isEmpty()
                || $submittedOptionIds->diff($validOptionIds)->isNotEmpty()
            ) {
                throw ValidationException::withMessages([
                    "conditions.{$conditionIndex}.parent_option_ids" => [
                        'One or more selected answers do not belong to the parent question.',
                    ],
                ]);
            }

            foreach ($submittedOptionIds as $parentOptionId) {
                $insertRows[] = [
                    'ai_guide_question_assignment_id' => (int) $assignment->id,
                    'parent_question_id' => $parentQuestionId,
                    'parent_option_id' => (int) $parentOptionId,
                    'operator' => 'selected',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($assignment, $insertRows) {
            DB::table('ai_guide_question_conditions')
                ->where(
                    'ai_guide_question_assignment_id',
                    $assignment->id
                )
                ->delete();

            if ($insertRows) {
                DB::table('ai_guide_question_conditions')
                    ->insert($insertRows);
            }
        });

        return response()->json([
            'message' => 'Conditional rules updated successfully.',
            'data' => [
                'condition_count' => collect($insertRows)
                    ->groupBy(fn ($row) => implode(':', [
                        $row['parent_question_id'],
                        $row['operator'],
                    ]))
                    ->count(),
            ],
        ]);
    }

    public function updateSortOrder(
        Request $request,
        AiGuideQuestion $question
    ): JsonResponse {
        $this->authorizeUpdate();

        $validated = $request->validate([
            'ai_category_id' => [
                'required',
                'integer',
                'exists:ai_categories,id',
            ],
            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $aiCategory = AiCategory::query()->findOrFail(
            (int) $validated['ai_category_id']
        );

        $assignment = $this
            ->categoryAssignments($aiCategory)
            ->first(
                fn ($row) =>
                    (int) $row->ai_guide_question_id
                    === (int) $question->id
            );

        if (!$assignment) {
            throw ValidationException::withMessages([
                'question' => [
                    'This question is not attached to the selected AI Product.',
                ],
            ]);
        }

        DB::table('ai_guide_question_assignments')
            ->where('id', $assignment->id)
            ->update([
                'sort_order' => (int) $validated['sort_order'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Question order updated successfully.',
            'data' => [
                'question_id' => (int) $question->id,
                'sort_order' => (int) $validated['sort_order'],
            ],
        ]);
    }

    private function categoryAssignments(
        AiCategory $aiCategory
    ): Collection {
        $morphTypes = array_values(array_unique([
            $aiCategory->getMorphClass(),
            'ai_category',
            AiCategory::class,
        ]));

        return DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $aiCategory->id)
            ->whereIn('assignable_type', $morphTypes)
            ->where(function ($query) {
                $query
                    ->whereNull('is_active')
                    ->orWhere('is_active', true);
            })
            ->get();
    }

    private function authorizeUpdate(): void
    {
        abort_unless(
            auth()->user()?->hasPermissionTo(
                'ai-guide-questions_update'
            ),
            403
        );
    }
}
