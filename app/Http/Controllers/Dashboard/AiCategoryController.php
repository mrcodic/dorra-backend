<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\Http\Requests\AiCategory\{StoreAiCategoryRequest, UpdateAiCategoryQuestionsRequest, UpdateAiCategoryRequest};
use App\Repositories\Interfaces\AiPromptTemplateRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\Ai\AiCategoryService;
use Illuminate\Http\JsonResponse;

class AiCategoryController extends DashboardController
{
    public function __construct(
        public AiCategoryService $aiCategoryService,
        public CategoryRepositoryInterface $categoryRepository,
        public AiPromptTemplateRepositoryInterface $promptTemplateRepository,
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
            'edit' => ['category', 'promptTemplate'],
            'store' => ['category', 'promptTemplate'],
            'update' => ['category', 'promptTemplate'],
        ];
        $questions = $this->aiGuideQuestionRepository
            ->query()
            ->where('is_active', true)
            ->with([
                'options' => fn($query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order')
            ])
            ->orderBy('sort_order')
            ->get();
        $this->assoiciatedData = [
            'create' => [
                'categories' => $this->categoryRepository->query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),

                'promptTemplates' => $this->promptTemplateRepository->query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
                'questions' => $questions,
            ],

            'edit' => [
                'categories' => $this->categoryRepository->query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
                'questions' => $questions,

                'promptTemplates' => $this->promptTemplateRepository->query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(),
            ],
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->aiCategoryService->getData();
    }
    public function questions(int $id)
    {
        $data = $this->aiCategoryService->getQuestionsConfiguration($id);
        return view('dashboard.ai-categories.questions', $data);
    }

    public function updateQuestions(UpdateAiCategoryQuestionsRequest $request, int $id): JsonResponse
    {
        $this->aiCategoryService->syncQuestions($id, $request->validated('questions') ?? []);
        return Response::api(message:'Questions updated successfully.');
    }
}
