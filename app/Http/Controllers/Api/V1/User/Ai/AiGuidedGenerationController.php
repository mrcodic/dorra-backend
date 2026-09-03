<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\AiGuidedGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AiGuidedGenerationController extends Controller
{
    public function __construct(
        private readonly AiGuidedGenerationService $aiGuidedGenerationService
    ) {
    }

    public function generate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ai_category_id' => [
                'required',
                'integer',
                'exists:ai_categories,id',
            ],

            'ai_studio_item_id' => [
                'required',
                'integer',
                'exists:ai_studio_items,id',
            ],

            'answers' => [
                'nullable',
                'array',
            ],
        ]);

        $result = $this->aiGuidedGenerationService->generate(
            user: $request->user(),
            aiCategoryId: $data['ai_category_id'],
            aiStudioItemId: $data['ai_studio_item_id'],
            answers: $data['answers'] ?? [],
        );

        return Response::api(
            data: $result
        );
    }
}
