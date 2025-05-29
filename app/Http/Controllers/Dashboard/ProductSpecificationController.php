<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\ProductSpecification\StoreProductSpecificationRequest;
use App\Http\Resources\Product\ProductSpecificationResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductSpecificationController extends Controller
{
    public function __construct(public ProductService $productService)
    {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreProductSpecificationRequest $request)
    {
        $productSpec = $this->productService->storeProductSpecification($request->validated());
        return Response::api(data: ProductSpecificationResource::make($productSpec));

    }

    public function getProductSpecs($productId)
    {
       $specs = $this->productService->productSpecifications($productId);
       return Response::api(data: ProductSpecificationResource::collection($specs));
    }
}
