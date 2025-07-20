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
        $products = $this->productService->getAll(relations: ['reviews','category:name','media' => fn($q) => $q->where('collection_name','product_main_image')], paginate: request()->boolean('paginate',true));
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



}

