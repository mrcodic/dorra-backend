<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Http\Resources\AiGuideQuestionResource;
use App\Services\AiGuideQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class AiGuideQuestionController extends Controller
{
    public function __construct(private readonly AiGuideQuestionService $service){}

    public function __invoke(): JsonResponse
    {
        $questions = $this->service->getActiveQuestions();

        return Response::api(
            data: AiGuideQuestionResource::collection($questions)
        );
    }
}
