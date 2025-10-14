<?php

namespace App\Http\Controllers\Api\V1\User\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;


class CategoryController extends Controller
{
    public function __construct(public CategoryService $categoryService)
    {
    }

    public function index()
    {
        $categories = $this->categoryService->getAll(relations: ['media',
            'landingSubCategories',
            'landingSubCategories.subCategoryProducts',
            'landingProducts',
            'products',
            'lastOffer'
            ],
            paginate: request('paginate',false),
            perPage: request('per_page',8));
        $categoryResourceCollection = $categories instanceof LengthAwarePaginator ?
            CategoryResource::collection($categories)->response()->getData()
            : CategoryResource::collection($categories);
        return Response::api(data: $categoryResourceCollection);
    }

    public function show($id, Request $request)
    {
        return Response::api(data: CategoryResource::make($this->categoryService->showResource($id,['media','specifications.options',
            'prices' => fn($q) => $request->query('all_prices') !== 'true' ? $q->orderBy('quantity')->limit(5) : null,
            'dimensions',
            'lastOffer'
        ])));
    }

    public function getSubCategories()
    {
        return Response::api(data: CategoryResource::collection($this->categoryService->getSubCategories()));
    }

}
