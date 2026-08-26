<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\AiGuideQuestion\StoreAiGuideQuestionRequest;
use App\Http\Requests\AiGuideQuestion\UpdateAiGuideQuestionRequest;
use App\Services\AiGuideQuestionService;

class AiGuideQuestionController extends DashboardController
{
    protected $indexView = 'ai-guide-questions.index';
    protected $createView = 'ai-guide-questions.create';
    protected $editView = 'ai-guide-questions.edit';
    protected $showView = 'ai-guide-questions.show';
    protected $usePagination = true;
    protected string $resourceTable = 'ai_guide_questions';

    protected array $methodRelations = [
        'index' => [],
        'show' => ['options'],
        'edit' => ['options'],
        'store' => ['options'],
        'update' => ['options'],
    ];

    public function __construct(AiGuideQuestionService $service)
    {
        parent::__construct($service);

        $this->storeRequestClass = new StoreAiGuideQuestionRequest();
        $this->updateRequestClass = new UpdateAiGuideQuestionRequest();
    }
}
