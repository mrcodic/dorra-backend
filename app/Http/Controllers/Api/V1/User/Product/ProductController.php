<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
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

    public function show(Product $product, Request $request)
    {
        return Response::api(data: ProductResource::make($this->productService->showResource($product->id,[
            'category',
            'prices' => function ($query) use ($request) {
                if ($request->query('all_prices') !== 'true') {
                    $query->limit(5);
                }},])));
    }


}

