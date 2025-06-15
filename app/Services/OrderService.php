<?php

namespace App\Services;

use App\Models\Product;
use App\Enums\Order\StatusEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\ValidDiscountCode;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\DesignRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;
use App\Repositories\Interfaces\ProductPriceRepositoryInterface;
use App\Repositories\Interfaces\ShippingAddressRepositoryInterface;
use App\Repositories\Interfaces\ProductSpecificationOptionRepositoryInterface;


class OrderService extends BaseService
{
    public function __construct(
        OrderRepositoryInterface                             $repository,
        public ProductPriceRepositoryInterface               $productPriceRepository,
        public ProductSpecificationOptionRepositoryInterface $specificationOptionRepository,
        public DiscountCodeRepositoryInterface               $discountCodeRepository,
        public UserRepositoryInterface                       $userRepository,
        public ProductRepositoryInterface                    $productRepository,
        public ShippingAddressRepositoryInterface            $shippingAddressRepository,
        public DesignRepositoryInterface                   $designRepository,
    )
    {
        parent::__construct($repository);
    }

    public function storeStep1($request): void
    {
        $request->validate(["user_id" => ["required", "exists:users,id"]]);
        $user = $this->userRepository->find($request->all()["user_id"]);
        $this->storeStepData(["user_info" => [
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "phone_number" => $user->phone_number,
        ]]);

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
        $product = $this->productRepository->find($request->product_id);
        $request->validate(["product_id" => ["required", "exists:products,id"]]);
        $this->storeStepData([
            "product_id" => $product->id,
            "product_name" => $product->name,
        ]);

    }
    public function templateCustomizations($request): void
    {
        $validatedData = $request->validate([
            "design_id" => ["required", "exists:designs,id"],
            "price_id" => ["required", "exists:product_prices,id"],
            "specs" => ["required", "array"],
            "specs.*.id" => ["required", "exists:product_specification_template,product_specification_id"],
            "specs.*.options" => ["required", "array"],
            "specs.*.options.*" => ["required", "exists:product_specification_options,id"],
        ]);
        $design = $this->designRepository->find($validatedData["design_id"]);

        $productPrice = $this->productPriceRepository->query(["id", "price","quantity"])->whereKey($validatedData["price_id"])->first();
        $specsPrices = collect($validatedData["specs"])
            ->flatMap(function ($spec) {
                return $this->specificationOptionRepository->query(["id", "price"])
                    ->whereIn("id", $spec["options"])
                    ->pluck("price");
            })
            ->sum();
            
        $subTotalPrice = $productPrice->price + $specsPrices;
        $this->storeStepData(["pricing_details" => ["sub_total" => $subTotalPrice, 'quantity' => $productPrice->quantity ?? 1 ],
            "design_info" => [
                "id" => $design->id,
                "design_image" => $design->getFirstMediaUrl("designs") ?: asset("images/default-photo.png"),
            ]]);
    }

    public function storeStep4($request): void
    {
        $this->storeStepData(["pricing_details" => $request->all()['orderData']]);
    }

    public function storeStep5($request): void
    {
        $this->storeStepData(["personal_info" => $request->except("_token")]);
    }

    public function storeStep6($request): void
    {
        $shippingAddress = $this->shippingAddressRepository->find($request->shipping_id);

        $this->storeStepData(["shipping_info" => [
            "id" => $shippingAddress->id,
            "label" => $shippingAddress->label,
            "line" => $shippingAddress->line,
            "state" => $shippingAddress->state->name,
            "country" => $shippingAddress->state->country->name,
        ]]);
    }

public function storeResource($validatedData, $relationsToStore = [], $relationsToLoad = [])
{
    // $order = $this->repository->create($validatedData);
    // $order->load($relationsToLoad);
    // $order->load($relationsToStore);
    // return $order;
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

    public function downloadPDF()
    {
        $orderData = Cache::get(getOrderStepCacheKey()) ?? [];
        $pdf = PDF::loadView('dashboard.orders.steps.step7', compact('orderData'));
        return $pdf->setPaper('a4')
            ->setOption('defaultFont', 'Helvetica')->download('order-confirmation.pdf');
    }
}
