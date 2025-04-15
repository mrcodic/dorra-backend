<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Product\{StoreProductRequest, UpdateProductRequest};
use App\Services\ProductService;


class ProductController extends DashboardController
{
   public function __construct(ProductService $productService)
   {
       parent::__construct($productService);
       $this->storeRequestClass = new StoreProductRequest();
       $this->updateRequestClass = new UpdateProductRequest();
       $this->indexView = 'products.index';
       $this->createView = 'products.create';
       $this->editView = 'products.edit';
       $this->showView = 'products.show';
       $this->usePagination = true;
   }
}
