<?php

namespace App\Http\Controllers\Api\V1\User\Category;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Response;


class CategoryController extends Controller
{
    public function __construct(public CategoryService $categoryService)
    {
    }

    public function index()
    {
        return Response::api(data: CategoryResource::collection($this->categoryService->getAll()));
    }

    public function show($id)
    {
        return Response::api(data: CategoryResource::make($this->categoryService->showResource($id)));
    }

    public function getSubCategories()
    {
        return Response::api(data: CategoryResource::collection($this->categoryService->getSubCategories()));
    }

}
