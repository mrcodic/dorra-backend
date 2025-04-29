<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Product\{StoreProductRequest, UpdateProductRequest};
use App\Services\ProductService;


class ProductController extends DashboardController
{
    public function __construct(
        public ProductService              $productService,
        public CategoryRepositoryInterface $categoryRepository,
        public TagRepositoryInterface      $tagRepository,)
    {

        parent::__construct($productService);
        $this->storeRequestClass = new StoreProductRequest();
        $this->updateRequestClass = new UpdateProductRequest();
        $this->indexView = 'products.index';
        $this->createView = 'products.create';
        $this->editView = 'products.edit';
        $this->showView = 'products.show';
        $this->assoiciatedData = [
            'shared' => [
                'categories' => $this->categoryRepository->query()->whereNull('parent_id')->get(['id', 'name']),
                'tags' => $this->tagRepository->all(columns: ['id', 'name']),
            ],
        ];
        $this->usePagination = true;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate($this->storeRequestClass->rules());
        $this->productService->storeResource($validatedData);
        return Response::api();
    }

    public function getData(): JsonResponse
    {
        return $this->productService->getData();
    }
}
