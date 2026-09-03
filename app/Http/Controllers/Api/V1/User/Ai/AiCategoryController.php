<?php

namespace App\Http\Controllers\Api\V1\User\Ai;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ai\AiCategoryResource;
use App\Services\Ai\AiCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AiCategoryController extends Controller
{
    public function __construct(private readonly AiCategoryService $aiCategoryService)
    {
    }

    public function index(Request $request)
    {
        $data = $request->validate([
            'paginate' => ['nullable', 'in:true,false'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $categories = $this->aiCategoryService
            ->getActiveCategories($request->boolean('paginate'), $data['per_page'] ?? 15);
        return Response::api(data:AiCategoryResource::collection($categories)->response()->getData());
    }
}
