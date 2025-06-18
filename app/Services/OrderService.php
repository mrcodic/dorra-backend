<?php

namespace App\Services;

use App\Models\Design;
use App\Models\Order;
use App\Models\Product;
use App\Enums\Order\StatusEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\ValidDiscountCode;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\Facades\DataTables;
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

        protected array $relations;



    public function __construct(
        OrderRepositoryInterface                             $repository,
        public ProductPriceRepositoryInterface               $productPriceRepository,
        public ProductSpecificationOptionRepositoryInterface $specificationOptionRepository,
        public DiscountCodeRepositoryInterface               $discountCodeRepository,
        public UserRepositoryInterface                       $userRepository,
        public ProductRepositoryInterface                    $productRepository,
        public ShippingAddressRepositoryInterface            $shippingAddressRepository,
        public DesignRepositoryInterface                    $designRepository,
    )


    {
        $this->relations = ['user', 'OrderAddress'];
        parent::__construct($repository);
    }



   public function getData()
{
    $orders = $this->repository
        ->query()
        ->with($this->relations) 
        ->withCount(['designs']) 
        ->when(request()->filled('search_value'), function ($query) {
            $locale = app()->getLocale();
            $search = request('search_value');
            $query->where("order_number->{$locale}", 'LIKE', "%{$search}%");
        })
        ->latest();

    return DataTables::of($orders)
        ->addColumn('order_number', function ($order) {
            return $order->order_number ?? '-';
        })
        ->addColumn('user_name', function ($order) {
            return $order->user
                ? ($order->user->first_name . ' ' . $order->user->last_name)
                : 'No User';
        })
        ->addColumn('items', function ($order) {
            return $order->designs_count ?? 0;
        })
        ->addColumn('total_price', function ($order) {
            return $order->total_price ? number_format($order->total_price, 2) : '0.00';
        })
        ->addColumn('status', function ($order) {
            return $order->status ? $order->status->label() : 'No Status';
        })
        ->addColumn('added_date', function ($order) {
            return $order->created_at ? $order->created_at->format('d/m/Y') : '-';
        })
        ->make();
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
        $validatedData = $request->validated();
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

    public function storeResource($validatedData = [], $relationsToStore = [], $relationsToLoad = [])
    {
        $cacheKey = getOrderStepCacheKey();
        $orderStepData = Cache::get($cacheKey, []);

        if (empty($orderStepData)) {
            throw new \Exception('Order step data not found in cache');
        }

        $orderData = [
            'user_id' => $orderStepData['user_info']['id'] ?? null,
            'product_id' => $orderStepData['product_id'] ?? null,
            'design_id' => $orderStepData['design_info']['id'] ?? null,
            'price_id' => $orderStepData['price_id'] ?? null,
            'quantity' => $orderStepData['pricing_details']['quantity'] ?? 1,
            'subtotal' => $orderStepData['pricing_details']['sub_total'] ?? 0,
            'total_price' => $orderStepData['pricing_details']['total'] ?? ($orderStepData['pricing_details']['sub_total'] ?? 0),
            'discount_amount' => $orderStepData['pricing_details']['discount'] ?? 0,
            'delivery_amount' => $orderStepData['pricing_details']['delivery'] ?? 0,
            'tax_amount' => $orderStepData['pricing_details']['tax'] ?? 0,
            'discount_code_id' => $orderStepData['pricing_details']['discount_code_id'] ?? null,
            'shipping_address_id' => $orderStepData['shipping_info']['id'] ?? null,
            'status' => StatusEnum::CONFIRMED,
            'notes' => $orderStepData['personal_info']['notes'] ?? null,
            'special_instructions' => $orderStepData['personal_info']['special_instructions'] ?? null,
            'order_number' => 'TEMP-' . uniqid(),
        ];

        $orderData = array_merge($orderData, $validatedData);
        $order = $this->repository->create($orderData);

        if (!empty($orderStepData['specs'])) {
            $this->attachSpecificationOptions($order, $orderStepData['specs']);
        }

        $this->saveOrderAddress($order, $orderStepData['personal_info'], $orderStepData['shipping_info']);

        $this->updateDesignOrderId($orderStepData['design_info']['id'] ?? null, $order->id);

        if (!empty($relationsToStore)) {
            foreach ($relationsToStore as $relation => $data) {
                $order->$relation()->create($data);
            }
        }

        if (!empty($relationsToLoad)) {
            $order->load($relationsToLoad);
        }

        Cache::forget($cacheKey);
        return $order;
    }

    private function attachSpecificationOptions($order, $specs): void
    {
        $specData = [];

        foreach ($specs as $spec) {
            foreach ($spec['options'] as $optionId) {
                $option = $this->specificationOptionRepository->find($optionId);
                $specData[$optionId] = [
                    'specification_id' => $spec['id'],
                    'price' => $option->price ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($specData)) {
            $order->specificationOptions()->attach($specData);
        }
    }

   private function saveOrderAddress($order, $personalInfo, $shippingInfo)
{
    $order->OrderAddress()->create([
        'order_id' => $order->id,
        'type' => 'shipping',
        'first_name' => $personalInfo['first_name'] ?? null,
        'last_name' => $personalInfo['last_name'] ?? null,
        'email' => $personalInfo['email'] ?? null,
        'phone' => $personalInfo['phone_number'] ?? null,
        'address_label' => $shippingInfo['label'] ?? null,
        'address_line' => $shippingInfo['line'] ?? null,
        'state' => $shippingInfo['state'] ?? null,
        'country' => $shippingInfo['country'] ?? null,
    ]);
}

private function updateDesignOrderId($designId, $orderId)
{
    if ($designId) {
        $design = Design::find($designId);
        if ($design) {
            $design->order_id = $orderId;
            $design->save();
        }
    }
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
    $pdf = Pdf::loadView('dashboard.orders.steps.step7', compact('orderData'));
    return $pdf->setPaper('a4')
        ->setOption('defaultFont', 'Helvetica')
        ->download('order-confirmation.pdf');
}
}
