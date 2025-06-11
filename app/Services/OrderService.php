<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductPriceRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationOptionRepositoryInterface;
use App\Rules\ValidDiscountCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;


class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface                             $repository,
        public ProductPriceRepositoryInterface               $productPriceRepository,
        public ProductSpecificationOptionRepositoryInterface $specificationOptionRepository,
        public DiscountCodeRepositoryInterface                 $discountCodeRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeStep1($request): void
    {
        $request->validate(["user_id" => ["required", "exists:users,id"]]);
        $this->storeStepData($request->only("user_id"));

    }

    public function storeStepData(array $stepData): void
    {
        $cacheKey = getOrderStepCacheKey();
        $existing = Cache::get($cacheKey, []);
        $merged = array_merge($existing, $stepData);

        Cache::put($cacheKey, $merged, now()->addHours(1));
    }

    public function storeStep2($request): void
    {
        $request->validate(["product_id" => ["required", "exists:products,id"]]);
        $this->storeStepData($request->only("product_id"));

    }

    public function storeStep4($request): void
    {
//        dd(...["pricing_details"=> $request->all()['orderData']]);
         $this->storeStepData(["pricing_details"=> $request->all()['orderData']]);
    }

    public function templateCustomizations($request)
    {
        $validatedData = $request->validate([
            "price_id" => ["required", "exists:product_prices,id"],
            "specs" => ["required", "array"],
            "specs.*.id" => ["required", "exists:product_specification_template,product_specification_id"],
            "specs.*.options" => ["required", "array"],
            "specs.*.options.*" => ["required", "exists:product_specification_options,id"],
        ]);
        $productPrice = $this->productPriceRepository->query(["id", "price"])->whereKey($validatedData["price_id"])->value("price");
        $specsPrices = collect($validatedData["specs"])
            ->flatMap(function ($spec) {
                return $this->specificationOptionRepository->query(["id", "price"])
                    ->whereIn("id", $spec["options"])
                    ->pluck("price");
            })
            ->sum();
        $subTotalPrice = $productPrice + $specsPrices;
        $this->storeStepData(["pricing_details" =>["sub_total" => $subTotalPrice]]);
    }

    public function applyDiscountCode($request)
    {
        $orderStepData = Cache::get(getOrderStepCacheKey());
        if (!$orderStepData || !isset($orderStepData['product_id'])) {
            throw new \Exception('Product information not found in order data');
        }

        $product = Product::find($orderStepData['product_id']);
        $category = $product->category;
        $validated = $request->validate([
            'code' => ['required', 'string', new ValidDiscountCode($product, $category)],
        ]);
        return $this->discountCodeRepository->query()->where($validated)->first();

    }

}
