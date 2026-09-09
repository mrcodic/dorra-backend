<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Ai\QuickStoreAiGuideQuestionRequest;
use App\Http\Requests\AiCategory\StoreAiCategoryRequest;
use App\Http\Requests\AiCategory\UpdateAiCategoryRequest;
use App\Models\AiStudioItem;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Ai\AiCategoryService;
use App\Services\Ai\AiGuideQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

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
            'edit' => [
                'category',
                'questions',
                'options',
                'studioItems',
            ],
            'store' => [
                'category',
                'questions',
                'options',
                'studioItems',
            ],
            'update' => [
                'category',
                'questions',
                'options',
                'studioItems',
            ],
        ];

        $categories = $this->categoryRepository
            ->query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $questions = $this->aiGuideQuestionRepository
            ->query()
            ->where('is_active', true)
            ->with([
                'options' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $studioItems = AiStudioItem::query()
            ->where('is_active', true)
            ->whereIn('key', [
                'image',
                'logo',
                'pattern',
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $associatedData = [
            'categories' => $categories,
            'questions' => $questions,
            'studioItems' => $studioItems,
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

    public function quickStoreQuestion(
        QuickStoreAiGuideQuestionRequest $request,
        AiGuideQuestionService $aiGuideQuestionService
    ): JsonResponse {
        $question = $aiGuideQuestionService->storeResource(
            $request->validated(),
            relationsToLoad: ['options']
        );

        $question->load('options');

        $isColorPalette = $question->options->contains(
            fn ($option) => !empty(
            data_get($option->ui_data, 'colors', [])
            )
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

            'options' => $question->options
                ->map(fn ($option) => [
                    'id' => $option->id,
                    'value' => $option->value,
                    'label' => $option->label,
                    'colors' => data_get(
                        $option->ui_data,
                        'colors',
                        []
                    ),
                ])
                ->values()
                ->all(),
        ]);
    }
}
