<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\Product\ProductResource;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DimensionRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Product\{StoreProductRequest, UpdateProductRequest};
use App\Services\ProductService;


class ProductController extends DashboardController
{
    public function __construct
    (
        public ProductService              $productService,
        public CategoryRepositoryInterface $categoryRepository,
        public TagRepositoryInterface      $tagRepository,
        public DimensionRepositoryInterface $dimensionRepository,
    )
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
                'dimensions' =>  $this->dimensionRepository->query()->whereIsCustom(false)->get(['id', 'name']),

            ],

        ];
        $this->methodRelations = [
            'index' => ['saves','orders'],
            'show' => ['category', 'tags', 'reviews.user', 'media', 'specifications.options.media'],
            'edit' => ['category', 'tags', 'reviews', 'media', 'specifications.options'],
        ];

        $this->usePagination = true;
        $this->resourceTable = 'products';
        $this->resourceClass = ProductResource::class;
    }

    public function getData(): JsonResponse
    {
        return $this->productService->getData();
    }

    public function search(Request $request)
    {
        $products = $this->productService->search($request);
        return view("dashboard.partials.filtered-products", compact('products'));
    }
}
