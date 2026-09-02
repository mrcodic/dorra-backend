<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Models\AiGuideQuestion;
use App\Repositories\Interfaces\AiCategoryRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AiCategoryService extends BaseService
{
    public function __construct(AiCategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(): JsonResponse
    {
        $aiCategories = $this->repository
            ->query()
            ->with(['category', 'promptTemplate'])
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
            ->when(request()->filled('generation_type'), function ($query) {
                $query->where('generation_type', request('generation_type'));
            })
            ->when(request()->filled('enabled'), function ($query) {
                $query->where('enabled', request('enabled'));
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        return DataTables::of($aiCategories)
            ->addColumn('category_name', function ($aiCategory) {
                return $aiCategory->category?->name;
            })
            ->addColumn('prompt_template_name', function ($aiCategory) {
                return $aiCategory->promptTemplate?->name ?: '-';
            })
            ->editColumn('generation_type', function ($aiCategory) {
                return $aiCategory->generation_type->value;
            })
            ->addColumn('generation_type_label', function ($aiCategory) {
                return $aiCategory->generation_type->label();
            })
            ->addColumn('action', function () {
                return [
                    'can_edit' => (bool)auth()->user()->hasPermissionTo('ai-categories_update'),
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('ai-categories_delete'),
                ];
            })
            ->make(true);
    }

    public function getQuestionsConfiguration(int $id): array
    {
        $aiCategory = $this->repository
            ->query()
            ->with([
                'category',
                'questions',
                'options',
            ])
            ->findOrFail($id);

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->with([
                'options' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'aiCategory' => $aiCategory,
            'questions' => $questions,
        ];
    }

    public function syncQuestions(int $id, array $questions = [])
    {
        return DB::transaction(function () use ($id, $questions) {
            $aiCategory = $this->repository
                ->query()
                ->with([
                    'questions',
                    'options',
                ])
                ->findOrFail($id);

            $selectedQuestions = collect($questions)
                ->filter(function ($question) {
                    return (bool)(
                        $question['selected']
                        ?? false
                    );
                });

            if ($selectedQuestions->isEmpty()) {
                $aiCategory
                    ->questions()
                    ->sync([]);

                $aiCategory
                    ->options()
                    ->sync([]);

                return $aiCategory;
            }

            $questionIds = $selectedQuestions
                ->pluck('question_id')
                ->map(fn($id) => (int)$id)
                ->unique()
                ->values();

            $availableQuestions = AiGuideQuestion::query()
                ->whereIn('id', $questionIds)
                ->where('is_active', true)
                ->with([
                    'options' => function ($query) {
                        $query->where(
                            'is_active',
                            true
                        );
                    },
                ])
                ->get()
                ->keyBy('id');

            $questionSync = [];
            $optionSync = [];

            foreach (
                $selectedQuestions as $index => $data
            ) {
                $questionId = (int)($data['question_id'] ?? 0);

                $question = $availableQuestions->get($questionId);

                if (!$question) {
                    continue;
                }

                $questionSync[$questionId] = [
                    'required' => (bool)(
                        $data['required']
                        ?? false
                    ),

                    'is_active' => true,

                    'sort_order' => (int)($data['sort_order'] ?? $index),

                    'options_mode' => null,
                ];

                $supportsOptions = in_array(
                    $question->type,
                    [
                        AiGuideQuestionTypeEnum::SINGLE_SELECT,
                        AiGuideQuestionTypeEnum::MULTI_SELECT,
                    ],
                    true
                );

                if (!$supportsOptions) {
                    continue;
                }

                $allowedOptionIds = $question
                    ->options
                    ->pluck('id')
                    ->map(fn($id) => (int)$id);

                $submittedOptionIds = collect(
                    $data['options']
                    ?? []
                )
                    ->map(fn($id) => (int)$id)
                    ->intersect(
                        $allowedOptionIds
                    )
                    ->unique()
                    ->values();

                foreach (
                    $submittedOptionIds as $optionIndex => $optionId
                ) {
                    $optionSync[$optionId] = [
                        'prompt_value_override' => null,
                        'is_active' => true,
                        'sort_order' => $optionIndex,
                    ];
                }
            }

            $aiCategory->questions()->sync($questionSync);

            $aiCategory->options()->sync($optionSync);

            return $aiCategory->fresh([
                'questions',
                'options',
            ]);
        });
    }
}
