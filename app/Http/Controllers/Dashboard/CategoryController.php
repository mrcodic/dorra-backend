<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Category\{StoreCategoryRequest, UpdateCategoryRequest};


class CategoryController extends DashboardController
{
    public function __construct(public CategoryService $categoryService)
    {
        parent::__construct($categoryService);
        $this->storeRequestClass = new StoreCategoryRequest();
        $this->updateRequestClass = new UpdateCategoryRequest();
        $this->indexView = 'categories.index';
        $this->usePagination = true;
        $this->resourceClass = CategoryResource::class;
        $this->resourceTable = 'categories';
    }

    public function getData(): JsonResponse
    {
        return $this->categoryService->getData();
    }

    public function search(Request $request)
    {
        $categories = $this->categoryService->search($request);
        return $this->resourceClass::collection($categories);
    }

    public function addToLanding(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => 'required', 'exists:categories,id',
            'sub_categories' => 'sometimes', 'array',
            'sub_categories.*' => ['sometimes', 'exists:categories,id'],
            'products' => ['sometimes', 'array'],
            'products.*' => ['sometimes', 'exists:products,id'],
        ],[
            'category_id.required' => 'Please select a product.', 
            'category_id.exists' => 'Selected product does not exist.',
        ]);
        $category = $this->categoryService->addToLanding($validatedData, $request->get('category_id'));
        return $this->resourceClass::make($category);

    }

    public function removeFromLanding(Request $request)
    {
        $request->validate(['category_id' => 'required', 'exists:categories,id']);
        $category = $this->categoryService->removeFromLanding($request->get('category_id'));
        return $this->resourceClass::make($category);

    }

}
