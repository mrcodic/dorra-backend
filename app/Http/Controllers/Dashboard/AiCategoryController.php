<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Ai\AiGenerationTypeEnum;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Ai\QuickStoreAiGuideQuestionRequest;
use App\Http\Requests\Ai\QuickStoreAiStudioItemRequest;
use App\Http\Requests\Ai\QuickUpdateAiStudioItemRequest;
use App\Http\Requests\AiCategory\StoreAiCategoryRequest;
use App\Http\Requests\AiCategory\UpdateAiCategoryRequest;
use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Models\AiStudioItem;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Ai\AiCategoryService;
use App\Services\Ai\AiGuideQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AiCategoryController extends DashboardController
{
    public function __construct(
        public AiCategoryService $aiCategoryService,
        public CategoryRepositoryInterface $categoryRepository,
        public AiGuideQuestionRepositoryInterface $aiGuideQuestionRepository,
    ) {
        parent::__construct($aiCategoryService);

        $this->storeRequestClass = new StoreAiCategoryRequest();
        $this->updateRequestClass = new UpdateAiCategoryRequest();

        $this->indexView = 'ai-categories.index';
        $this->createView = 'ai-categories.create';
        $this->editView = 'ai-categories.edit';

        $this->usePagination = true;
        $this->resourceTable = 'ai_categories';

        $this->methodRelations = [
            'edit' => ['category', 'questions', 'options', 'studioItems'],
            'store' => ['category', 'questions', 'options', 'studioItems'],
            'update' => ['category', 'questions', 'options', 'studioItems'],
        ];

        $categories = $this->categoryRepository->query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $routeAiCategory = request()->route('ai_category')
            ?? request()->route('aiCategory')
            ?? request()->route('id');

        $currentAiCategoryId = $routeAiCategory instanceof \App\Models\AiCategory
            ? $routeAiCategory->id
            : (is_numeric($routeAiCategory) ? (int) $routeAiCategory : null);
        $questionConditions = $this->getAiCategoryQuestionConditions($currentAiCategoryId);
        $aiCategoryMorph = (new \App\Models\AiCategory())->getMorphClass();

        $questions = $this->aiGuideQuestionRepository->query()
            ->where('is_active', true)
            ->where(function ($query) use ($currentAiCategoryId, $aiCategoryMorph) {
                $query->whereNotExists(function ($subQuery) use ($aiCategoryMorph) {
                    $subQuery
                        ->selectRaw('1')
                        ->from('ai_guide_question_assignments')
                        ->whereColumn(
                            'ai_guide_question_assignments.ai_guide_question_id',
                            'ai_guide_questions.id'
                        )
                        ->where(
                            'ai_guide_question_assignments.assignable_type',
                            $aiCategoryMorph
                        );
                });

                if ($currentAiCategoryId) {
                    $query->orWhereExists(function ($subQuery) use ($currentAiCategoryId, $aiCategoryMorph) {
                        $subQuery
                            ->selectRaw('1')
                            ->from('ai_guide_question_assignments')
                            ->whereColumn(
                                'ai_guide_question_assignments.ai_guide_question_id',
                                'ai_guide_questions.id'
                            )
                            ->where(
                                'ai_guide_question_assignments.assignable_type',
                                $aiCategoryMorph
                            )
                            ->where(
                                'ai_guide_question_assignments.assignable_id',
                                $currentAiCategoryId
                            );
                    });
                }
            })
            ->with([
                'options' => fn($query) => $query
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $studioItems = AiStudioItem::query()
            ->with([
                'questions' => fn($query) => $query
                    ->select('ai_guide_questions.id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $studioItemQuestionIds = $studioItems
            ->mapWithKeys(fn($studioItem) => [
                $studioItem->id => $this->studioItemQuestionIds($studioItem),
            ])
            ->all();

        /*
         * Parent-condition source.
         *
         * Keep the Product Questions list scoped exactly as before, but make
         * sure conditionQuestionData also knows about questions that belong
         * only to Studio Items. The Blade/JS filters this broader source to
         * the questions actually available for the current Product context:
         *
         * - questions directly attached to the AI Product
         * - questions attached to Studio Items currently selected on Product
         */
        $conditionQuestionIds = collect($questions)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->merge(
                collect($studioItemQuestionIds)
                    ->flatten()
                    ->map(fn($id) => (int) $id)
            )
            ->filter()
            ->unique()
            ->values();

        $conditionQuestions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $conditionQuestionIds)
            ->with([
                'options' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $associatedData = [
            'categories' => $categories,
            'questions' => $questions,
            'conditionQuestions' => $conditionQuestions,
            'studioItems' => $studioItems,
            'studioItemQuestionIds' => $studioItemQuestionIds,
            'questionConditions' => $questionConditions,
        ];

        $this->assoiciatedData = [
            'create' => $associatedData,
            'edit' => $associatedData,
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->aiCategoryService->getData();
    }

    public function quickStoreQuestion(QuickStoreAiGuideQuestionRequest $request, AiGuideQuestionService $aiGuideQuestionService): JsonResponse
    {
        $data = $request->validated();
        $aiCategoryId = Arr::pull($data, 'ai_category_id');

        $question = DB::transaction(function () use ($data, $aiCategoryId, $aiGuideQuestionService) {
            $question = $aiGuideQuestionService->storeResource($data, relationsToLoad: ['options']);
            $question->load('options');

            if ($aiCategoryId) {
                $aiCategory = AiCategory::query()->findOrFail($aiCategoryId);
                $this->attachQuestionToAiCategory($aiCategory, $question);
            }

            return $question;
        });

        $isColorPalette = $question->options->contains(
            fn($option) => !empty(data_get($option->ui_data, 'colors', []))
        );

        return Response::api(data: [
            'id' => $question->id,
            'key' => $question->key,
            'title' => $question->title,
            'prompt_label' => $question->prompt_label,
            'type' => $question->type?->value ?? $question->type,
            'type_label' => $question->type?->label() ?? (string) $question->type,
            'required' => (bool) $question->required,
            'sort_order' => (int) ($question->sort_order ?? 0),
            'isColorPalette' => $isColorPalette,
            'options' => $question->options->map(fn($option) => [
                'id' => $option->id,
                'value' => $option->value,
                'label' => $option->label,
                'colors' => data_get($option->ui_data, 'colors', []),
            ])->values()->all(),
        ]);
    }
    private function attachQuestionToAiCategory(AiCategory $aiCategory, AiGuideQuestion $question): void
    {
        $morphType = $aiCategory->getMorphClass();
        $now = now();

        DB::table('ai_guide_question_assignments')->updateOrInsert(
            [
                'ai_guide_question_id' => $question->id,
                'assignable_type' => $morphType,
                'assignable_id' => $aiCategory->id,
            ],
            [
                'required' => (bool) $question->required,
                'is_active' => true,
                'sort_order' => (int) ($question->sort_order ?? 0),
                'options_mode' => 'all',
                'updated_at' => $now,
            ]
        );

        foreach ($question->options as $index => $option) {
            if (!(bool) ($option->is_active ?? true)) continue;

            DB::table('ai_guide_option_assignments')->updateOrInsert(
                [
                    'ai_guide_question_option_id' => $option->id,
                    'assignable_type' => $morphType,
                    'assignable_id' => $aiCategory->id,
                ],
                [
                    'prompt_value_override' => null,
                    'is_active' => true,
                    'sort_order' => $index,
                    'updated_at' => $now,
                ]
            );
        }
    }
    public function quickStoreStudioItem(QuickStoreAiStudioItemRequest $request): JsonResponse
    {
        $data = $request->validated();
        $questionIds = array_values($data['question_ids'] ?? []);
        unset($data['question_ids']);

        $studioItem = DB::transaction(function () use ($data, $questionIds) {
            $data['key'] = $this->generateStudioItemKey((string) data_get($data, 'name.en', 'studio-item'));
            $studioItem = AiStudioItem::query()->create($data);
            $this->syncStudioItemQuestions($studioItem, $questionIds);
            return $studioItem->refresh();
        });

        return Response::api(data: $this->studioItemPayload($studioItem), message: 'Studio Item created successfully.');
    }

    public function quickUpdateStudioItem(QuickUpdateAiStudioItemRequest $request, AiStudioItem $studioItem): JsonResponse
    {
        $data = $request->validated();
        $questionIds = array_values($data['question_ids'] ?? []);
        unset($data['question_ids']);

        DB::transaction(function () use ($studioItem, $data, $questionIds) {
            if (array_key_exists('settings', $data)) {
                $data['settings'] = array_replace($studioItem->settings ?? [], $data['settings'] ?? []);
            }

            unset($data['key']);
            $studioItem->update($data);
            $this->syncStudioItemQuestions($studioItem, $questionIds);
        });

        $studioItem->refresh();

        return Response::api(data: $this->studioItemPayload($studioItem), message: 'Studio Item updated successfully.');
    }

    public function quickDeleteStudioItem(AiStudioItem $studioItem): JsonResponse
    {
        abort_unless(auth()->user()?->hasPermissionTo('ai-studio-items_delete'), 403);

        DB::transaction(function () use ($studioItem) {
            if (Schema::hasTable('ai_category_studio_items')) {
                DB::table('ai_category_studio_items')->where('ai_studio_item_id', $studioItem->id)->delete();
            }

            $assignableTypes = $this->studioItemMorphTypes($studioItem);

            if (Schema::hasTable('ai_guide_question_assignments')) {
                DB::table('ai_guide_question_assignments')->where('assignable_id', $studioItem->id)->whereIn('assignable_type', $assignableTypes)->delete();
            }

            if (Schema::hasTable('ai_guide_option_assignments')) {
                DB::table('ai_guide_option_assignments')->where('assignable_id', $studioItem->id)->whereIn('assignable_type', $assignableTypes)->delete();
            }

            $studioItem->delete();
        });

        return Response::api(message: 'Studio Item deleted successfully.');
    }

    private function syncStudioItemQuestions(AiStudioItem $studioItem, array $questionIds): void
    {
        $questionIds = collect($questionIds)->map(fn($id) => (int) $id)->filter()->unique()->values();

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->whereIn('id', $questionIds)
            ->with('options')
            ->get()
            ->keyBy('id');

        if ($questions->count() !== $questionIds->count()) {
            throw ValidationException::withMessages([
                'question_ids' => ['One or more selected questions are unavailable.'],
            ]);
        }

        $assignableTypes = $this->studioItemMorphTypes($studioItem);
        $morphType = $studioItem->getMorphClass();

        DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $studioItem->id)
            ->whereIn('assignable_type', $assignableTypes)
            ->delete();

        DB::table('ai_guide_option_assignments')
            ->where('assignable_id', $studioItem->id)
            ->whereIn('assignable_type', $assignableTypes)
            ->delete();

        if ($questionIds->isEmpty()) return;

        $now = now();
        $questionRows = [];
        $optionRows = [];

        foreach ($questionIds as $sortOrder => $questionId) {
            $question = $questions->get($questionId);

            $questionRows[] = [
                'ai_guide_question_id' => $questionId,
                'assignable_type' => $morphType,
                'assignable_id' => $studioItem->id,
                'required' => null,
                'is_active' => true,
                'sort_order' => $sortOrder,
                'options_mode' => 'all',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (!in_array($question->type?->value ?? $question->type, ['single_select', 'multi_select'], true)) continue;

            foreach ($question->options->filter(fn($option) => (bool) ($option->is_active ?? true))->values() as $optionSort => $option) {
                $optionRows[] = [
                    'ai_guide_question_option_id' => $option->id,
                    'assignable_type' => $morphType,
                    'assignable_id' => $studioItem->id,
                    'prompt_value_override' => null,
                    'is_active' => true,
                    'sort_order' => $optionSort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($questionRows) DB::table('ai_guide_question_assignments')->insert($questionRows);
        if ($optionRows) DB::table('ai_guide_option_assignments')->insert($optionRows);
    }

    private function studioItemPayload(AiStudioItem $studioItem): array
    {
        $studioItem->load('questions');

        $generationType = $studioItem->generation_type;
        $generationTypeValue = $generationType instanceof AiGenerationTypeEnum ? $generationType->value : (string) $generationType;
        $generationTypeLabel = $generationType instanceof AiGenerationTypeEnum ? $generationType->label() : Str::headline($generationTypeValue);
        $questionIds = $studioItem->questions->pluck('id')->map(fn($id) => (int) $id)->values()->all();

        if (!$questionIds) {
            $questionIds = $this->studioItemQuestionIds($studioItem);
        }

        return [
            'id' => $studioItem->id,
            'key' => $studioItem->key,
            'name' => $studioItem->name,
            'name_en' => $studioItem->getTranslation('name', 'en', false),
            'name_ar' => $studioItem->getTranslation('name', 'ar', false),
            'description' => $studioItem->description,
            'description_en' => $studioItem->getTranslation('description', 'en', false),
            'description_ar' => $studioItem->getTranslation('description', 'ar', false),
            'generation_type' => $generationTypeValue,
            'generation_type_label' => $generationTypeLabel,
            'credits_cost' => (int) $studioItem->credits_cost,
            'sort_order' => (int) $studioItem->sort_order,
            'is_active' => (bool) $studioItem->is_active,
            'settings' => $studioItem->settings ?? [],
            'question_ids' => $questionIds,
            'question_count' => count($questionIds),
        ];
    }

    private function studioItemQuestionIds(AiStudioItem $studioItem): array
    {
        if (!Schema::hasTable('ai_guide_question_assignments')) return [];

        return DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $studioItem->id)
            ->whereIn('assignable_type', $this->studioItemMorphTypes($studioItem))
            ->where(function ($query) {
                $query->whereNull('is_active')->orWhere('is_active', true);
            })
            ->orderByRaw('CASE WHEN sort_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('ai_guide_question_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function studioItemMorphTypes(AiStudioItem $studioItem): array
    {
        return array_values(array_unique([$studioItem->getMorphClass(), 'ai_studio_item', AiStudioItem::class]));
    }

    private function generateStudioItemKey(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'studio_item';
        $key = $base;
        $counter = 2;

        while (AiStudioItem::query()->where('key', $key)->exists()) {
            $key = $base . '_' . $counter;
            $counter++;
        }

        return $key;
    }
    private function getAiCategoryQuestionConditions(?int $aiCategoryId): array
    {
        if (
            !$aiCategoryId
            || !Schema::hasTable('ai_guide_question_assignments')
            || !Schema::hasTable('ai_guide_question_conditions')
        ) {
            return [];
        }

        $aiCategory = AiCategory::query()->find($aiCategoryId);

        if (!$aiCategory) {
            return [];
        }

        $morphTypes = array_values(array_unique([
            $aiCategory->getMorphClass(),
            'ai_category',
            AiCategory::class,
        ]));

        $assignments = DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $aiCategory->id)
            ->whereIn('assignable_type', $morphTypes)
            ->get(['id', 'ai_guide_question_id']);

        if ($assignments->isEmpty()) {
            return [];
        }

        $questionIdByAssignmentId = $assignments
            ->mapWithKeys(fn($assignment) => [
                (int) $assignment->id => (int) $assignment->ai_guide_question_id,
            ]);

        return DB::table('ai_guide_question_conditions')
            ->whereIn('ai_guide_question_assignment_id', $questionIdByAssignmentId->keys())
            ->get()
            ->mapWithKeys(function ($condition) use ($questionIdByAssignmentId) {
                $questionId = $questionIdByAssignmentId->get(
                    (int) $condition->ai_guide_question_assignment_id
                );

                if (!$questionId) {
                    return [];
                }

                return [
                    $questionId => [
                        'parent_question_id' => (int) $condition->parent_question_id,
                        'parent_option_id' => (int) $condition->parent_option_id,
                        'operator' => $condition->operator ?: 'selected',
                    ],
                ];
            })
            ->all();
    }
}
