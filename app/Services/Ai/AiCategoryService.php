<?php

namespace App\Services\Ai;

use App\Models\AiCategory;
use App\Models\AiGuideQuestion;
use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class AiCategoryService extends BaseService
{
    public function __construct(
        AiCategoryRepositoryInterface $repository,
        private readonly AiCategoryGenerationConfigService $generationConfigService
    ) {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $aiCategories = $this->repository->query()
            ->with(['category', 'studioItems'])
            ->withCount('questions')
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');

                    $query->whereHas('category', function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('enabled'), fn($query) => $query->where('enabled', request('enabled')))
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($aiCategories)
            ->addColumn('category_name', fn($aiCategory) => $aiCategory->category?->name)
            ->addColumn('studio_items', fn($aiCategory) => $aiCategory->studioItems
                ->pluck('name')
                ->filter()
                ->values()
                ->all()
            )
            ->addColumn('action', fn() => [
                'can_edit' => (bool) auth()->user()->hasPermissionTo('ai-categories_update'),
                'can_delete' => (bool) auth()->user()->hasPermissionTo('ai-categories_delete'),
            ])
            ->make(true);
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return DB::transaction(function () use ($validatedData, $relationsToLoad) {
            $questions = Arr::pull($validatedData, 'questions', []);
            $studioItems = Arr::pull($validatedData, 'studio_items', []);

            $aiCategory = $this->repository->create($validatedData);

            $this->generationConfigService->sync(
                $aiCategory->id,
                $questions,
                $studioItems
            );
            $this->syncQuestionConditions($aiCategory, $questions);

            return $aiCategory->load(array_unique(array_merge(
                $relationsToLoad,
                ['category', 'questions', 'options', 'studioItems']
            )));
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        return DB::transaction(function () use ($validatedData, $id, $relationsToLoad) {
            $questions = Arr::pull($validatedData, 'questions', []);
            $studioItems = Arr::pull($validatedData, 'studio_items', []);

            $aiCategory = $this->repository->update($validatedData, $id);

            $this->generationConfigService->sync(
                $aiCategory->id,
                $questions,
                $studioItems
            );
            $this->syncQuestionConditions($aiCategory, $questions);

            return $aiCategory->load(array_unique(array_merge(
                $relationsToLoad,
                ['category', 'questions', 'options', 'studioItems']
            )));
        });
    }

    public function getActiveCategories(bool $paginate = false, int $perPage = 15)
    {
        $query = $this->repository->query()
            ->where('enabled', true)
            ->with(['category', 'studioItems'])
            ->orderBy('sort_order')
            ->orderBy('id');

        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }
    private function syncQuestionConditions(AiCategory $aiCategory, array $rows): void
    {

        $selectedRows = collect($rows)
            ->mapWithKeys(function ($row, $key) {
                $questionId = (int) ($row['question_id'] ?? $key);

                return $questionId
                    ? [$questionId => $row]
                    : [];
            })
            ->filter(fn($row) => (bool) ($row['selected'] ?? false));

        $morphTypes = array_values(array_unique([
            $aiCategory->getMorphClass(),
            'ai_category',
            AiCategory::class,
        ]));

        $assignments = DB::table('ai_guide_question_assignments')
            ->where('assignable_id', $aiCategory->id)
            ->whereIn('assignable_type', $morphTypes)
            ->get()
            ->keyBy(fn($assignment) => (int) $assignment->ai_guide_question_id);

        if ($assignments->isNotEmpty()) {
            DB::table('ai_guide_question_conditions')
                ->whereIn(
                    'ai_guide_question_assignment_id',
                    $assignments->pluck('id')
                )
                ->delete();
        }

        if ($selectedRows->isEmpty()) {
            return;
        }

        $questions = AiGuideQuestion::query()
            ->whereIn('id', $selectedRows->keys())
            ->with([
                'options' => fn($query) => $query->where('is_active', true),
            ])
            ->get()
            ->keyBy('id');

        $now = now();
        $inserts = [];

        foreach ($selectedRows as $questionId => $row) {
            if (!(bool) ($row['condition_enabled'] ?? false)) {
                continue;
            }

            $condition = $row['condition'] ?? [];

            $parentQuestionId = (int) ($condition['parent_question_id'] ?? 0);
            $parentOptionId = (int) ($condition['parent_option_id'] ?? 0);
            $operator = (string) ($condition['operator'] ?? 'selected');

            if (!$parentQuestionId || !$parentOptionId) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition" => [
                        'Parent question and answer are required.',
                    ],
                ]);
            }

            if ($parentQuestionId === (int) $questionId) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.parent_question_id" => [
                        'A question cannot depend on itself.',
                    ],
                ]);
            }

            if (!$selectedRows->has($parentQuestionId)) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.parent_question_id" => [
                        'The parent question must be selected for this AI Product.',
                    ],
                ]);
            }

            $parentQuestion = $questions->get($parentQuestionId);

            if (!$parentQuestion) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.parent_question_id" => [
                        'The selected parent question is unavailable.',
                    ],
                ]);
            }

            $parentOption = $parentQuestion->options->firstWhere(
                'id',
                $parentOptionId
            );

            if (!$parentOption) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.parent_option_id" => [
                        'The selected answer does not belong to the parent question.',
                    ],
                ]);
            }

            if (!in_array($operator, ['selected', 'not_selected'], true)) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.operator" => [
                        'Invalid conditional operator.',
                    ],
                ]);
            }

            $childSort = (int) ($row['sort_order'] ?? 0);
            $parentSort = (int) data_get(
                $selectedRows->get($parentQuestionId),
                'sort_order',
                0
            );

            if ($parentSort >= $childSort) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}.condition.parent_question_id" => [
                        'The parent question must appear before the conditional question.',
                    ],
                ]);
            }

            $assignment = $assignments->get((int) $questionId);

            if (!$assignment) {
                throw ValidationException::withMessages([
                    "questions.{$questionId}" => [
                        'Question assignment was not created.',
                    ],
                ]);
            }

            $inserts[] = [
                'ai_guide_question_assignment_id' => $assignment->id,
                'parent_question_id' => $parentQuestionId,
                'parent_option_id' => $parentOptionId,
                'operator' => $operator,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($inserts) {
            DB::table('ai_guide_question_conditions')->insert($inserts);
        }
    }
}
