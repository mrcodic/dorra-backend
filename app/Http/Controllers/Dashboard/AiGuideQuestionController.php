<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\AiGuideQuestion\{StoreAiGuideQuestionRequest, UpdateAiGuideQuestionRequest};
use App\Services\AiGuideQuestionService;
use Illuminate\Http\JsonResponse;

class AiGuideQuestionController extends DashboardController
{
    public function __construct(
        public AiGuideQuestionService $aiGuideQuestionService,
    ) {
        parent::__construct($aiGuideQuestionService);

        $this->storeRequestClass = new StoreAiGuideQuestionRequest();
        $this->updateRequestClass = new UpdateAiGuideQuestionRequest();

        $this->indexView = 'ai-guide-questions.index';
        $this->createView = 'ai-guide-questions.create';
        $this->editView = 'ai-guide-questions.edit';
        $this->showView = 'ai-guide-questions.show';

        $this->usePagination = true;
        $this->resourceTable = 'ai_guide_questions';

        $this->methodRelations = [
            'index' => [],
            'show' => ['options'],
            'edit' => ['options'],
            'store' => ['options'],
            'update' => ['options'],
        ];
    }

    public function getData(): JsonResponse
    {
        return $this->aiGuideQuestionService->getData();
    }
}
