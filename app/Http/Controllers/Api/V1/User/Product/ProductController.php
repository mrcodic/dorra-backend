<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function __construct(public ProductService $productService)
    {
    }

    public function index()
    {
        $products = $this->productService->getAll(relations: ['reviews']);
        $productResourceCollection = $products instanceof LengthAwarePaginator ?
            ProductResource::collection($products)->response()->getData()
            : ProductResource::collection($products);

        return Response::api(data: $productResourceCollection);
    }

    public function show(Product $product, Request $request)
    {
        return Response::api(data: ProductResource::make($this->productService->showResource($product->id, [
            'category',
            'templates',
            'prices' => function ($query) use ($request) {
                if ($request->query('all_prices') !== 'true') {
                    $query->limit(5);
                }
            },])));
    }


}

