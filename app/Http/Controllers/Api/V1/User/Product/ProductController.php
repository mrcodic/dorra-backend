<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function __construct(public ProductService $productService)
    {
    }

    public function index()
    {
       return Response::api(data: ProductResource::collection($this->productService->getAll()));
    }

    public function show(Product $product)
    {
        return Response::api(data: ProductResource::make($product->load(['prices'])));
    }

}
