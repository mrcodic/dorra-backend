<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Product\{StoreProductRequest, UpdateProductRequest};
use App\Services\ProductService;


class ProductController extends DashboardController
{
   public function __construct(public ProductService $productService, public CategoryRepositoryInterface $categoryRepository)
   {

       parent::__construct($productService);
       $this->storeRequestClass = new StoreProductRequest();
       $this->updateRequestClass = new UpdateProductRequest();
       $this->indexView = 'products.index';
       $this->createView = 'products.create';
       $this->editView = 'products.edit';
       $this->showView = 'products.show';
       $this->assoiciatedData['create'] = [
           'categories' => $this->categoryRepository->query()->whereNull('parent_id')->get(['id', 'name']),
       ];
       $this->usePagination = true;
   }
    public function getData():JsonResponse
    {
        return $this->productService->getData();
    }
}
