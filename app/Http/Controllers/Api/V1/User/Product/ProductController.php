<?php

namespace App\Http\Controllers\Api\V1\User\Product;

use App\Enums\Product\TypeEnum;
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
        $products = $this->productService->getAll(relations: ['reviews','category:name','media'],
            paginate: request()->boolean('paginate',true),
           perPage: request()->integer('per_page',9)
        );
        $productResourceCollection = $products instanceof LengthAwarePaginator ?
            ProductResource::collection($products)->response()->getData()
            : ProductResource::collection($products);

        return Response::api(data: $productResourceCollection);
    }

    public function show(Product $product, Request $request)
    {
        return Response::api(data: ProductResource::make($this->productService->showResource($product->id, [
            'category:id,name',
            'templates',
            'media',
            'specifications.options',
            'prices' => fn($q) => $request->query('all_prices') !== 'true' ? $q->limit(5) : null,
        ])));
    }

    public function productTypes()
    {
        $types = collect(TypeEnum::availableTypes())
            ->map(fn($type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])
            ->values();

        return Response::api(data: $types);
    }

    public function getQuantities($productId)
    {

        $quantities = $this->productService->getQuantities($productId);
        $quantities = $quantities ?: (object)[];
        return Response::api(data: $quantities);


    }



}

