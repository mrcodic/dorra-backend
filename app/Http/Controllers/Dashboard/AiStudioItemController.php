<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use Illuminate\Http\Request;
use App\Http\Requests\AiStudioItem\{
    StoreAiStudioItemRequest,
    UpdateAiStudioItemRequest,
    UpdateAiStudioItemQuestionsRequest
};
use App\Repositories\Interfaces\AiGuideQuestionRepositoryInterface;
use App\Services\Ai\AiStudioItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class AiStudioItemController extends DashboardController
{
    public function __construct(
        public AiStudioItemService $aiStudioItemService,
        public AiGuideQuestionRepositoryInterface $aiGuideQuestionRepository,
    ) {
        parent::__construct($aiStudioItemService);

        $this->storeRequestClass = new StoreAiStudioItemRequest();
        $this->updateRequestClass = new UpdateAiStudioItemRequest();

        $this->indexView = 'ai-studio-items.index';
        $this->createView = 'ai-studio-items.create';
        $this->editView = 'ai-studio-items.edit';

        $this->usePagination = true;
        $this->resourceTable = 'ai_studio_items';

        $this->methodRelations = [
            'edit' => ['promptTemplate'],
            'store' => ['promptTemplate'],
            'update' => ['promptTemplate'],
        ];

        $questions = $this->aiGuideQuestionRepository
            ->query()
            ->where('is_active', true)
            ->with([
                'options' => fn($query) => $query
                    ->orderBy('sort_order')
            ])
            ->orderBy('sort_order')
            ->get();

        $this->assoiciatedData = [
            'create' => [
                'questions' => $questions,
            ],

            'edit' => [
                'questions' => $questions,
            ],
        ];
    }

    public function getData(Request $request): JsonResponse
    {
        return $this->aiStudioItemService->getData($request);
    }

    public function questions(int $id)
    {
        $data = $this->aiStudioItemService
            ->getQuestionsConfiguration($id);

        return view(
            'dashboard.ai-studio-items.questions',
            $data
        );
    }

    public function updateQuestions(
        UpdateAiStudioItemQuestionsRequest $request,
        int $id
    ): JsonResponse {
        $this->aiStudioItemService->syncQuestions(
            $id,
            $request->validated('questions') ?? []
        );

        return Response::api(
            message: 'Questions updated successfully.'
        );
    }
}
