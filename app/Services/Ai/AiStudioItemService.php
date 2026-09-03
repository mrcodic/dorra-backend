<?php

namespace App\Services\Ai;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Models\AiGuideQuestion;
use App\Repositories\Interfaces\AiStudioItemRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AiStudioItemService extends BaseService
{
    public function __construct(AiStudioItemRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getData(Request $request): JsonResponse
    {
        $locale = app()->getLocale();

        $query = $this->repository->query();

        if ($request->filled('search_value')) {
            $search = $request->search_value;

            $query->where(function ($q) use ($search, $locale) {
                $q->where("name->{$locale}", 'like', "%{$search}%")->orWhere(
                    'key', 'like', "%{$search}%");
            });
        }

        if ($request->filled('generation_type')) {
            $query->where('generation_type', $request->generation_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return DataTables::of(
            $query->orderBy('sort_order')
        )
            ->addColumn('name', function ($item) {
                return $item->name;
            })

            ->addColumn('generation_type_label', function ($item) {
                return $item->generation_type?->label()
                    ?? $item->generation_type;
            })

            ->addColumn('image', function ($item) {
                return $item->getFirstMediaUrl('image') ?: null;
            })

            ->addColumn('action', function ($item) {
                return [
                    'can_edit' => auth()
                            ->user()
                            ?->can('ai-studio-items_update')
                        ?? false,

                    'can_delete' => auth()
                            ->user()
                            ?->can('ai-studio-items_delete')
                        ?? false,
                ];
            })

            ->make(true);
    }
    public function getActiveItems(
        bool $paginate = false,
        int $perPage = 15
    ) {
        $query = $this->repository->query()
            ->where('is_active', true)
            ->orderBy('sort_order');
        return $paginate
            ? $query->paginate($perPage)
            : $query->get();
    }

    public function getQuestionsConfiguration(int $id): array
    {
        $studioItem = $this->repository
            ->query()
            ->with([
                'questions',
                'options',
            ])
            ->findOrFail($id);

        $questions = AiGuideQuestion::query()
            ->where('is_active', true)
            ->with('options')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return [
            'studioItem' => $studioItem,
            'questions' => $questions,
        ];
    }

    public function syncQuestions(
        int $id,
        array $questions = []
    ) {
        return DB::transaction(function () use ($id, $questions) {
            $studioItem = $this->repository
                ->query()
                ->with([
                    'questions',
                    'options',
                ])
                ->findOrFail($id);

            $selectedQuestions = collect($questions)
                ->filter(
                    fn($question) =>
                    (bool) ($question['selected'] ?? false)
                );

            if ($selectedQuestions->isEmpty()) {
                $studioItem->questions()->sync([]);
                $studioItem->options()->sync([]);

                return $studioItem;
            }

            $questionIds = $selectedQuestions
                ->pluck('question_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            $availableQuestions = AiGuideQuestion::query()
                ->whereIn('id', $questionIds)
                ->where('is_active', true)
                ->with('options')
                ->get()
                ->keyBy('id');

            $questionSync = [];
            $optionSync = [];

            foreach ($selectedQuestions as $index => $data) {
                $questionId = (int) (
                    $data['question_id'] ?? 0
                );

                $question = $availableQuestions->get(
                    $questionId
                );

                if (!$question) {
                    continue;
                }

                $questionSync[$questionId] = [
                    'required' => (bool) (
                        $data['required'] ?? false
                    ),
                    'is_active' => true,
                    'sort_order' => (int) (
                        $data['sort_order'] ?? $index
                    ),
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
                    ->map(fn($id) => (int) $id);

                $submittedOptionIds = collect(
                    $data['options'] ?? []
                )
                    ->map(fn($id) => (int) $id)
                    ->intersect($allowedOptionIds)
                    ->unique()
                    ->values();

                foreach (
                    $submittedOptionIds
                    as $optionIndex => $optionId
                ) {
                    $optionSync[$optionId] = [
                        'prompt_value_override' => null,
                        'is_active' => true,
                        'sort_order' => $optionIndex,
                    ];
                }
            }

            $studioItem
                ->questions()
                ->sync($questionSync);

            $studioItem
                ->options()
                ->sync($optionSync);

            return $studioItem->fresh([
                'questions',
                'options',
            ]);
        });
    }
}
