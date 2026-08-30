<?php

namespace App\Services;

use App\Enums\Ai\AiGuideQuestionTypeEnum;
use App\Repositories\Interfaces\AiGuideQuestionOptionRepositoryInterface;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class AiGuideQuestionService extends BaseService
{
    public function __construct(
        AiGuideQuestionRepositoryInterface $repository,
        public AiGuideQuestionOptionRepositoryInterface $optionRepository
    ) {
        parent::__construct($repository);
    }
    public function getData(): JsonResponse
    {
        $locale = app()->getLocale();

        $questions = $this->repository
            ->query()
            ->withCount('options')
            ->when(request()->filled('search_value'), function ($query) use ($locale) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = strtolower(request('search_value'));

                    $query->where(function ($query) use ($search, $locale) {
                        $query->whereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$search}%"]
                        )->orWhereRaw(
                            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(prompt_label, '$.\"{$locale}\"'))) LIKE ?",
                            ["%{$search}%"]
                        );
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->when(request()->filled('type'), function ($query) {
                $query->where('type', request('type'));
            })
            ->when(request()->filled('is_active'), function ($query) {
                $query->where('is_active', request('is_active'));
            })
            ->orderBy('sort_order')
            ->latest();

        return DataTables::of($questions)
            ->editColumn('title', function ($question) {
                return $question->title;
            })
            ->editColumn('type', function ($question) {
                return $question->type->value;
            })
            ->editColumn('prompt_label', function ($question) {
                return $question->prompt_label;
            })
            ->addColumn('type_label', function ($question) {
                return $question->type->label();
            })
            ->addColumn('action', function () {
                return [
                    'can_edit' => (bool)auth()->user()->hasPermissionTo('ai-guide-questions_update'),
                    'can_delete' => (bool)auth()->user()->hasPermissionTo('ai-guide-questions_delete'),
                ];
            })
            ->make(true);
    }
    public function getAll($relations = [], bool $paginate = false, $columns = ['*'], $perPage = 10, $counts = [])
    {
        $perPage = request('per_page', $perPage);

        $query = $this->repository->query()
            ->with($relations)
            ->withCount('options')
            ->when(request()->filled('search_value'), function ($query) {
                $search = request('search_value');

                $query->where(function ($query) use ($search) {
                    $query->where('key', 'like', "%{$search}%")
                        ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.en'))) LIKE ?", ['%' . strtolower($search) . '%'])
                        ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, '$.ar'))) LIKE ?", ['%' . strtolower($search) . '%']);
                });
            })
            ->when(request()->filled('type'), fn($query) => $query->where('type', request('type')))
            ->when(request()->has('is_active') && request('is_active') !== '', fn($query) => $query->where('is_active', request()->boolean('is_active')))
            ->orderBy('sort_order')
            ->orderBy('id');

        return $paginate ? $query->paginate($perPage)->withQueryString() : $query->get();
    }

    public function getActiveQuestions()
    {
        return $this->repository->query()
            ->with('options')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $relationsToLoad) {
            $options = Arr::pull($validatedData, 'options', []);
            $validatedData['key'] = (string) Str::ulid();

            $question = $this->repository->create($validatedData);

            $this->syncOptions($question->id, $question->type, $options);

            return $question->load($relationsToLoad);
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        return $this->handleTransaction(function () use ($validatedData, $id, $relationsToLoad) {
            $options = Arr::pull($validatedData, 'options', []);
            $question = $this->repository->update($validatedData, $id);

            $this->syncOptions($question->id, $question->type, $options);

            return $question->load($relationsToLoad);
        });
    }

    private function syncOptions(int $questionId, AiGuideQuestionTypeEnum $type, array $options): void
    {
        if ($type !== AiGuideQuestionTypeEnum::SINGLE_SELECT) {
            $this->optionRepository->query()
                ->where('ai_guide_question_id', $questionId)
                ->delete();

            return;
        }

        $submittedIds = [];

        foreach (array_values($options) as $index => $option) {
            $optionId = $option['id'] ?? null;
            $value = $this->generateOptionValue(
                $questionId,
                $option['label']['en'],
                $optionId
            );

            $data = [
                'ai_guide_question_id' => $questionId,
                'value' => $value,
                'label' => $option['label'],
                'prompt_value' => $option['prompt_value'] ?? null,
                'sort_order' => $index,
            ];

            if ($optionId) {
                $model = $this->optionRepository->query()
                    ->where('ai_guide_question_id', $questionId)
                    ->find($optionId);

                if ($model) {
                    $model->update($data);
                    $submittedIds[] = $model->id;
                    continue;
                }
            }

            $model = $this->optionRepository->create($data);
            $submittedIds[] = $model->id;
        }

        $query = $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId);

        if ($submittedIds) {
            $query->whereNotIn('id', $submittedIds);
        }

        $query->delete();
    }

    private function generateOptionValue(int $questionId, string $label, ?int $ignoreId = null): string
    {
        $baseValue = Str::slug($label, '_') ?: 'option';
        $value = $baseValue;
        $counter = 2;

        while ($this->optionValueExists($questionId, $value, $ignoreId)) {
            $value = "{$baseValue}_{$counter}";
            $counter++;
        }

        return $value;
    }

    private function optionValueExists(int $questionId, string $value, ?int $ignoreId = null): bool
    {
        return $this->optionRepository->query()
            ->where('ai_guide_question_id', $questionId)
            ->where('value', $value)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }
}
