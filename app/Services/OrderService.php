<?php

namespace App\Services;

use Exception;
use App\Models\Order;
use App\Models\Design;
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
        $design = $this->designRepository->query()->find($validatedData['design_id']);

        $this->handleTransaction(function () use ($validatedData, $design) {
            $this->designRepository->update($validatedData, $validatedData['design_id']);
            $syncData = collect($validatedData['specs'])->mapWithKeys(function ($spec) {
                return [
                    $spec['id'] => ['spec_option_id' => $spec['option']]
                ];
            })->toArray();

            $design->specifications()->sync($syncData);
        });

        $this->storeStepData(["pricing_details" => ["sub_total" => $design->total_price, 'quantity' => $design->productPrice->quantity ?? 1],
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
            throw new Exception('Order step data not found in cache');
        }

        $orderData = [
            'user_id' => $orderStepData['user_info']['id'] ?? null,
            'subtotal' => $orderStepData['pricing_details']['sub_total'] ?? 0,
            'total_price' => $orderStepData['pricing_details']['total'] ?? 0,
            'discount_amount' => $orderStepData['pricing_details']['discount'] ?? 0,
            'delivery_amount' => $orderStepData['pricing_details']['delivery'] ?? 0,
            'tax_amount' => $orderStepData['pricing_details']['tax'] ?? 0,
            'status' => StatusEnum::CONFIRMED,
            'order_number' => 'TEMP-' . uniqid(),
        ];

        $orderData = array_merge($orderData, $validatedData);
        $order = $this->repository->create($orderData);

        if (!empty($orderStepData['specs'])) {
            $this->attachSpecificationOptions($order, $orderStepData['specs']);
        }

        $this->saveOrderAddress($order, $orderStepData['personal_info'], $orderStepData['shipping_info']);

        if (!empty($orderStepData['designs'])) {
            $this->attachDesignsToOrder($order, $orderStepData['designs']);
        }

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
            'shipping_address_id' => $shippingInfo['id'] ?? null,
            'address_label' => $shippingInfo['label'] ?? null,
            'address_line' => $shippingInfo['line'] ?? null,
            'state' => $shippingInfo['state'] ?? null,
            'country' => $shippingInfo['country'] ?? null,
        ]);
    }

    private function attachDesignsToOrder($order, $designsData)
    {
        $pivotData = [];

        foreach ($designsData as $designData) {
            $design = Design::with(['product', 'productPrice'])->find($designData['id']);

            if (!$design) {
                continue;
            }

            $quantity = $designData['quantity'] ?? 1;

            if ($design->product_price_id && $design->productPrice) {
                $customProductPrice = $design->productPrice->price;
                $basePrice = 0;
                $totalPrice = $customProductPrice * $quantity;
            } else {
                $customProductPrice = 0;
                $basePrice = $design->product ? $design->product->base_price : 0;
                $totalPrice = $basePrice * $quantity;
            }

            $pivotData[$design->id] = [
                'quantity' => $quantity,
                'base_price' => $basePrice,
                'custom_product_price' => $customProductPrice,
                'total_price' => $totalPrice,
            ];
        }

        $order->designs()->attach($pivotData);
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
