<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use App\Repositories\Interfaces\AiStudioItemRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class AiStudioItemService extends BaseService
{
    public function __construct(
        AiStudioItemRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getData(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        $query = $this->repository->query();

        if ($request->filled('search_value')) {
            $search = $request->search_value;

            $query->where(function ($q) use ($search, $locale) {
                $q
                    ->where(
                        "name->{$locale}",
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'key',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('generation_type')) {
            $query->where(
                'generation_type',
                $request->generation_type
            );
        }

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        return DataTables::of(
            $query->orderBy('sort_order')
        )
            ->addColumn(
                'name',
                fn ($item) => $item->name
            )
            ->addColumn(
                'generation_type_label',
                function ($item) {
                    return $item->generation_type?->label()
                        ?? $item->generation_type;
                }
            )
            ->addColumn(
                'image',
                function ($item) {
                    return $item
                        ->getFirstMediaUrl('image')
                        ?: null;
                }
            )
            ->addColumn(
                'action',
                function ($item) {
                    return [
                        'can_edit' =>
                            auth()
                                ->user()
                                ?->can(
                                    'ai-studio-items_update'
                                )
                            ?? false,

                        'can_delete' =>
                            auth()
                                ->user()
                                ?->can(
                                    'ai-studio-items_delete'
                                )
                            ?? false,
                    ];
                }
            )
            ->make(true);
    }

    public function getActiveItems(
        bool $paginate = false,
        int $perPage = 15,
        ?int $aiCategoryId = null
    ) {
        $query = $this->repository
            ->query()
            ->where('is_active', true)
            ->when(
                $aiCategoryId,
                function ($query) use ($aiCategoryId) {
                    $query->whereHas(
                        'aiCategories',
                        function ($query) use ($aiCategoryId) {
                            $query->where(
                                'ai_categories.id',
                                $aiCategoryId
                            );
                        }
                    );
                }
            )
            ->orderBy('sort_order');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }

    /*
     |--------------------------------------------------------------------------
     | Main Store / Update integration
     |--------------------------------------------------------------------------
     |
     | Pull `questions` before the base repository save so it is never treated
     | as an ai_studio_items table column.
     |
     | BaseService still owns all existing Studio Item behavior including
     | normal model persistence/media handling.
     |
     */

    public function storeResource(
        $validatedData,
        $relationsToStore = [],
        $relationsToLoad = []
    ) {
        $hasQuestionsPayload =
            array_key_exists(
                'questions',
                $validatedData
            );

        $questions =
            $validatedData['questions']
            ?? [];

        unset(
            $validatedData['questions']
        );

        $studioItem =
            parent::storeResource(
                $validatedData,
                $relationsToStore,
                $relationsToLoad
            );

        if ($hasQuestionsPayload) {
            $studioItem =
                $this->syncQuestions(
                    (int) $studioItem->id,
                    $questions
                );
        }

        return $relationsToLoad
            ? $studioItem->load(
                $relationsToLoad
            )
            : $studioItem;
    }

    public function updateResource(
        $validatedData,
        $id,
        $relationsToLoad = []
    ) {
        $hasQuestionsPayload =
            array_key_exists(
                'questions',
                $validatedData
            );

        $questions =
            $validatedData['questions']
            ?? [];

        unset(
            $validatedData['questions']
        );

        $studioItem =
            parent::updateResource(
                $validatedData,
                $id,
                $relationsToLoad
            );

        /*
         * Very important:
         * if `questions` was not submitted, do NOT erase existing mappings.
         */
        if ($hasQuestionsPayload) {
            $studioItem =
                $this->syncQuestions(
                    (int) $studioItem->id,
                    $questions
                );
        }

        return $relationsToLoad
            ? $studioItem->load(
                $relationsToLoad
            )
            : $studioItem;
    }

    /*
     |--------------------------------------------------------------------------
     | Edit configuration
     |--------------------------------------------------------------------------
     */

    public function getQuestionsConfiguration(
        int $id
    ): array {
        $studioItem = $this->repository
            ->query()
            ->with([
                'questions',
                'options',
            ])
            ->findOrFail($id);

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->with([
                'options' => fn ($query) =>
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $morphTypes =
            $this->studioItemMorphTypes(
                $studioItem
            );

        $assignments =
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
                ->where(function ($query) {
                    $query
                        ->whereNull('is_active')
                        ->orWhere(
                            'is_active',
                            true
                        );
                })
                ->orderByRaw(
                    'CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END'
                )
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        $conditionsByAssignmentId =
            collect();

        if ($assignments->isNotEmpty()) {
            $conditionsByAssignmentId =
                DB::table(
                    'ai_guide_question_conditions'
                )
                    ->whereIn(
                        'ai_guide_question_assignment_id',
                        $assignments->pluck('id')
                    )
                    ->orderBy('id')
                    ->get()
                    ->groupBy(
                        fn ($row) =>
                        (int) $row
                            ->ai_guide_question_assignment_id
                    );
        }

        $configuration =
            $assignments
                ->mapWithKeys(
                    function (
                        $assignment
                    ) use (
                        $conditionsByAssignmentId
                    ) {
                        $conditionGroups =
                            collect(
                                $conditionsByAssignmentId
                                    ->get(
                                        (int) $assignment->id,
                                        collect()
                                    )
                            )
                                ->groupBy(
                                    fn ($condition) =>
                                    implode(':', [
                                        (int) $condition
                                            ->parent_question_id,

                                        (string) (
                                        $condition
                                            ->operator
                                            ?: 'selected'
                                        ),
                                    ])
                                )
                                ->map(
                                    function ($rows) {
                                        $first =
                                            $rows->first();

                                        return [
                                            'parent_question_id' =>
                                                (int) $first
                                                    ->parent_question_id,

                                            'parent_option_ids' =>
                                                $rows
                                                    ->pluck(
                                                        'parent_option_id'
                                                    )
                                                    ->map(
                                                        fn ($id) =>
                                                        (int) $id
                                                    )
                                                    ->unique()
                                                    ->values()
                                                    ->all(),

                                            'operator' =>
                                                (string) (
                                                $first
                                                    ->operator
                                                    ?: 'selected'
                                                ),
                                        ];
                                    }
                                )
                                ->values()
                                ->all();

                        return [
                            (int) $assignment
                                ->ai_guide_question_id => [
                                'selected' => true,

                                'required' =>
                                    $assignment->required === null
                                        ? null
                                        : (bool) $assignment->required,

                                'sort_order' =>
                                    (int) (
                                        $assignment->sort_order
                                        ?? 0
                                    ),

                                'conditions' =>
                                    $conditionGroups,
                            ],
                        ];
                    }
                );

        return [
            'studioItem' => $studioItem,
            'questions' => $questions,
            'configuration' =>
                $configuration,
        ];
    }

    /*
     |--------------------------------------------------------------------------
     | Question + option + condition sync
     |--------------------------------------------------------------------------
     |
     | Same parent + many option ids = OR.
     | Different parent rows = AND.
     |
     */

    public function syncQuestions(
        int $id,
        array $questions = []
    ) {
        return DB::transaction(
            function () use (
                $id,
                $questions
            ) {
                /** @var AiStudioItem $studioItem */
                $studioItem =
                    $this->repository
                        ->query()
                        ->with([
                            'questions',
                            'options',
                        ])
                        ->findOrFail($id);

                /*
                 * Supports both payload styles:
                 *
                 * 1. full form:
                 *    selected: true / false
                 *
                 * 2. compact AJAX:
                 *    send selected rows only, without `selected`
                 */
                $selectedQuestions =
                    collect($questions)
                        ->filter(
                            function ($row) {
                                if (
                                    !array_key_exists(
                                        'selected',
                                        $row
                                    )
                                ) {
                                    return true;
                                }

                                return (bool) (
                                    $row['selected']
                                    ?? false
                                );
                            }
                        )
                        ->map(
                            function (
                                $row,
                                $index
                            ) {
                                return [
                                    'question_id' =>
                                        (int) (
                                            $row[
                                            'question_id'
                                            ]
                                            ?? 0
                                        ),

                                    'required' =>
                                        (bool) (
                                            $row[
                                            'required'
                                            ]
                                            ?? false
                                        ),

                                    'sort_order' =>
                                        (int) (
                                            $row[
                                            'sort_order'
                                            ]
                                            ?? $index
                                        ),

                                    'conditions' =>
                                        collect(
                                            $row[
                                            'conditions'
                                            ]
                                            ?? []
                                        )
                                            ->values()
                                            ->all(),
                                ];
                            }
                        )
                        ->filter(
                            fn ($row) =>
                                $row[
                                'question_id'
                                ] > 0
                        )
                        ->unique(
                            'question_id'
                        )
                        ->values();

                $oldAssignmentIds =
                    $this
                        ->studioQuestionAssignments(
                            $studioItem
                        )
                        ->pluck('id');

                /*
                 * Clearing all questions must also clear all condition rows.
                 */
                if (
                    $selectedQuestions
                        ->isEmpty()
                ) {
                    if (
                        $oldAssignmentIds
                            ->isNotEmpty()
                    ) {
                        DB::table(
                            'ai_guide_question_conditions'
                        )
                            ->whereIn(
                                'ai_guide_question_assignment_id',
                                $oldAssignmentIds
                            )
                            ->delete();
                    }

                    $studioItem
                        ->questions()
                        ->sync([]);

                    $studioItem
                        ->options()
                        ->sync([]);

                    return $studioItem
                        ->fresh([
                            'questions',
                            'options',
                        ]);
                }

                $questionIds =
                    $selectedQuestions
                        ->pluck(
                            'question_id'
                        )
                        ->unique()
                        ->values();

                $availableQuestions =
                    AiGuideQuestion::query()
                        ->whereIn(
                            'id',
                            $questionIds
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->with([
                            'options' =>
                                fn ($query) =>
                                $query
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->orderBy(
                                        'sort_order'
                                    )
                                    ->orderBy('id'),
                        ])
                        ->get()
                        ->keyBy(
                            fn ($question) =>
                            (int) $question->id
                        );

                if (
                    $availableQuestions
                        ->count()
                    !== $questionIds->count()
                ) {
                    throw ValidationException::withMessages([
                        'questions' => [
                            'One or more selected questions are unavailable.',
                        ],
                    ]);
                }

                $selectedByQuestionId =
                    $selectedQuestions
                        ->keyBy(
                            'question_id'
                        );

                /*
                 * Validate conditions before changing any pivot rows.
                 */
                foreach (
                    $selectedQuestions
                    as $rowIndex => $data
                ) {
                    $childQuestionId =
                        (int) $data[
                        'question_id'
                        ];

                    $childSort =
                        (int) $data[
                        'sort_order'
                        ];

                    foreach (
                        $data['conditions']
                        as $conditionIndex =>
                        $condition
                    ) {
                        $parentQuestionId =
                            (int) (
                                $condition[
                                'parent_question_id'
                                ]
                                ?? 0
                            );

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

                        if (
                            !$selectedByQuestionId
                                ->has(
                                    $parentQuestionId
                                )
                        ) {
                            throw ValidationException::withMessages([
                                "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                                    'The parent question must also be attached to this Studio Item.',
                                ],
                            ]);
                        }

                        $parentQuestion =
                            $availableQuestions
                                ->get(
                                    $parentQuestionId
                                );

                        if (
                            !$parentQuestion
                            || !$this
                                ->supportsOptions(
                                    $parentQuestion
                                )
                        ) {
                            throw ValidationException::withMessages([
                                "questions.{$rowIndex}.conditions.{$conditionIndex}.parent_question_id" => [
                                    'The parent question must support selectable answers.',
                                ],
                            ]);
                        }

                        /*
                         * Current runtime visibility resolver is order-sensitive.
                         * Parent must come before child.
                         */
                        $parentSort =
                            (int) (
                                $selectedByQuestionId
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
                                    'The parent question must have a lower sort order than the child question.',
                                ],
                            ]);
                        }

                        $validOptionIds =
                            $parentQuestion
                                ->options
                                ->pluck('id')
                                ->map(
                                    fn ($optionId) =>
                                    (int) $optionId
                                );

                        $submittedOptionIds =
                            collect(
                                $condition[
                                'parent_option_ids'
                                ]
                                ?? []
                            )
                                ->map(
                                    fn ($optionId) =>
                                    (int) $optionId
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

                /*
                 * Remove the old condition rows BEFORE sync(), because sync()
                 * may keep an existing pivot row instead of deleting it.
                 */
                if (
                    $oldAssignmentIds
                        ->isNotEmpty()
                ) {
                    DB::table(
                        'ai_guide_question_conditions'
                    )
                        ->whereIn(
                            'ai_guide_question_assignment_id',
                            $oldAssignmentIds
                        )
                        ->delete();
                }

                $questionSync = [];
                $optionSync = [];

                foreach (
                    $selectedQuestions
                    as $data
                ) {
                    $questionId =
                        (int) $data[
                        'question_id'
                        ];

                    $question =
                        $availableQuestions
                            ->get(
                                $questionId
                            );

                    $questionSync[
                    $questionId
                    ] = [
                        'required' =>
                            (bool) $data[
                            'required'
                            ],

                        'is_active' =>
                            true,

                        'sort_order' =>
                            (int) $data[
                            'sort_order'
                            ],

                        /*
                         * Studio form exposes all active options.
                         */
                        'options_mode' =>
                            'all',
                    ];

                    if (
                        !$this
                            ->supportsOptions(
                                $question
                            )
                    ) {
                        continue;
                    }

                    foreach (
                        $question
                            ->options
                            ->values()
                        as $optionIndex =>
                        $option
                    ) {
                        $optionSync[
                        (int) $option->id
                        ] = [
                            'prompt_value_override' =>
                                null,

                            'is_active' =>
                                true,

                            'sort_order' =>
                                (int) $optionIndex,
                        ];
                    }
                }

                $studioItem
                    ->questions()
                    ->sync(
                        $questionSync
                    );

                $studioItem
                    ->options()
                    ->sync(
                        $optionSync
                    );

                /*
                 * Resolve the real assignment IDs after sync.
                 * Conditions FK to ai_guide_question_assignments.id.
                 */
                $assignmentIdByQuestionId =
                    $this
                        ->studioQuestionAssignments(
                            $studioItem
                        )
                        ->keyBy(
                            fn ($assignment) =>
                            (int) $assignment
                                ->ai_guide_question_id
                        )
                        ->map(
                            fn ($assignment) =>
                            (int) $assignment->id
                        );

                $conditionRows = [];
                $now = now();

                foreach (
                    $selectedQuestions
                    as $data
                ) {
                    $childQuestionId =
                        (int) $data[
                        'question_id'
                        ];

                    $assignmentId =
                        $assignmentIdByQuestionId
                            ->get(
                                $childQuestionId
                            );

                    if (!$assignmentId) {
                        throw ValidationException::withMessages([
                            'questions' => [
                                'Question assignment was not created.',
                            ],
                        ]);
                    }

                    foreach (
                        $data['conditions']
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
                                    fn ($optionId) =>
                                    (int) $optionId
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

                /*
                 * De-duplicate only identical DB rows.
                 * Same option IDs across different child questions/rules remain valid.
                 */
                $conditionRows =
                    collect(
                        $conditionRows
                    )
                        ->unique(
                            fn ($row) =>
                            implode(':', [
                                $row[
                                'ai_guide_question_assignment_id'
                                ],
                                $row[
                                'parent_question_id'
                                ],
                                $row[
                                'parent_option_id'
                                ],
                                $row[
                                'operator'
                                ],
                            ])
                        )
                        ->values()
                        ->all();

                if ($conditionRows) {
                    DB::table(
                        'ai_guide_question_conditions'
                    )
                        ->insert(
                            $conditionRows
                        );
                }

                return $studioItem
                    ->fresh([
                        'questions',
                        'options',
                    ]);
            }
        );
    }

    private function supportsOptions(
        AiGuideQuestion $question
    ): bool {
        return in_array(
            $question->type,
            [
                AiGuideQuestionTypeEnum::SINGLE_SELECT,
                AiGuideQuestionTypeEnum::MULTI_SELECT,
            ],
            true
        );
    }

    private function studioQuestionAssignments(
        AiStudioItem $studioItem
    ): Collection {
        return DB::table(
            'ai_guide_question_assignments'
        )
            ->where(
                'assignable_id',
                $studioItem->id
            )
            ->whereIn(
                'assignable_type',
                $this->studioItemMorphTypes(
                    $studioItem
                )
            )
            ->get();
    }

    private function studioItemMorphTypes(
        AiStudioItem $studioItem
    ): array {
        return array_values(
            array_unique([
                $studioItem
                    ->getMorphClass(),

                'ai_studio_item',

                AiStudioItem::class,
            ])
        );
    }
}
