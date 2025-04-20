<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Http\Controllers\Controller;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(public ProductService $productService)
    {
    }

    public function index()
    {
        $this->productService->getAll();
    }

}
