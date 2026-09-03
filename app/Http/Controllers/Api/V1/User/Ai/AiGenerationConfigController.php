<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\GetAiQuestionsRequest;
use App\Http\Resources\Ai\AiGuideQuestionResource;
use App\Services\Ai\AiGenerationConfigService;
use Illuminate\Support\Facades\Response;

class AiGenerationConfigController extends Controller
{
    public function __construct(private readonly AiGenerationConfigService $aiGenerationConfigService)
    {
    }

    public function questions(GetAiQuestionsRequest $request)
    {
        $data = $request->validated();
        $questions = $this
            ->aiGenerationConfigService
            ->getAssignedQuestions(
                $data['ai_category_id'],
                $data['ai_studio_item_id']
            );
        return Response::api(data:AiGuideQuestionResource::collection($questions));
    }
}
