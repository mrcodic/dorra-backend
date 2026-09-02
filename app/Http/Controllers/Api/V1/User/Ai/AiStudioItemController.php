<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ai\AiStudioItemResource;
use App\Services\Ai\AiStudioItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AiStudioItemController extends Controller
{
    public function __construct(private readonly AiStudioItemService $aiStudioItemService)
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'paginate' => ['nullable', 'in:true,false'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $items = $this->aiStudioItemService->getActiveItems($request->boolean('paginate'), $data['per_page'] ?? 15);
        return Response::api(data: AiStudioItemResource::collection($items)->response()->getData());
    }
}
