<?php

namespace App\Services;


use App\DTOs\Payment\PaymentRequestData;
use App\Services\Payment\PaymentGatewayFactory;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Rules\ValidDiscountCode;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use App\Enums\Order\{OrderTypeEnum, StatusEnum};
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\{DB, Auth, Cache};
use App\Models\{Order, Design, Product, Location, ShippingAddress};
use App\DTOs\Order\{OrderData, OrderAddressData, OrderItemData, PickupContactData};
use App\Repositories\Interfaces\{PaymentMethodRepositoryInterface,
    UserRepositoryInterface,
    OrderRepositoryInterface,
    DesignRepositoryInterface,
    ProductRepositoryInterface,
    LocationRepositoryInterface,
    DiscountCodeRepositoryInterface,
    ProductPriceRepositoryInterface,
    ShippingAddressRepositoryInterface,
    ProductSpecificationOptionRepositoryInterface
};


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
        public DesignRepositoryInterface                     $designRepository,
        public LocationRepositoryInterface                   $locationRepository,
        public CartService                                   $cartService,
        public PaymentMethodRepositoryInterface              $paymentMethodRepository,
        public PaymentGatewayFactory                         $paymentFactory,

    )


    {
        $this->relations = [
            'user.addresses.state.country',
            'orderAddress',
            'pickupContact'
        ];
        parent::__construct($repository);
    }

    public function userOrders()
    {
        return $this->repository->query()
            ->whereBelongsTo(auth('sanctum')->user())
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->when(request('search'), function ($query) {
                $query->whereOrderNumber(request('search'));
            })
            ->with(['orderItems.product', 'paymentMethod'])
            ->latest()
            ->paginate();
    }

    public function showUserOrder($id)
    {
        return $this->repository->query()
            ->whereBelongsTo(auth('sanctum')->user())
            ->whereKey($id)
            ->when(request('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->with(['orderItems', 'orderItems.specs'])
            ->firstOrFail();
    }

    public function getData()
    {
        $orders = $this->repository
            ->query()
            ->with($this->relations)
            ->withCount(['orderItems'])
            ->when(request()->filled('search_value'), function ($query) {
                if (hasMeaningfulSearch(request('search_value'))) {
                    $search = request('search_value');
                    $query->where("order_number", 'LIKE', "%{$search}%");
                } else {
                    $query->whereRaw('1 = 0');
                }
            })->when(request()->filled('created_at'), function ($query) {
                $query->orderBy('created_at', request('created_at'));
            })->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })

            ->latest();

        return DataTables::of($orders)
            ->addColumn('order_number', function ($order) {
                return $order->order_number ?? '-';
            })
            ->addColumn('user_name', function ($order) {
                return $order->user
                    ? ($order->user->first_name . ' ' . $order->user->last_name)
                    : 'Guest';
            })
            ->addColumn('items', function ($order) {
                return $order->order_items_count ?? 0;
            })
            ->addColumn('total_price', function ($order) {
                return $order->total_price ?? 0;
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
        $type = OrderTypeEnum::from($request->type);

        Cache::put('order_type_' . Auth::id(), $type->value, now()->addMinutes(30));

        if ($type === OrderTypeEnum::PICKUP) {
            $pickupAddress = $this->locationRepository->find($request->location_id);

            $this->storeStepData([
                "pickup_info" => [
                    "id" => $pickupAddress->id,
                    "location_name" => $pickupAddress->name,
                    "line" => $pickupAddress->address_line,
                    "state" => $pickupAddress->state->name,
                    "country" => $pickupAddress->state->country->name,
                ],
                "shipping_info" => null
            ]);

            Cache::put('pickup_contact_' . Auth::id(), [
                'first_name' => $request->pickup_first_name,
                'last_name' => $request->pickup_last_name,
                'email' => $request->pickup_email,
                'phone' => $request->pickup_phone,
            ], now()->addMinutes(30));
        }

        if ($type === OrderTypeEnum::SHIPPING) {
            $shippingAddress = $this->shippingAddressRepository->find($request->shipping_id);

            $this->storeStepData([
                "shipping_info" => [
                    "id" => $shippingAddress->id,
                    "label" => $shippingAddress->label,
                    "line" => $shippingAddress->line,
                    "state" => $shippingAddress->state->name,
                    "country" => $shippingAddress->state->country->name,
                ],
                "pickup_info" => null
            ]);
        }
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
        ];

        $orderData = array_merge($orderData, $validatedData);
        $order = $this->repository->create($orderData);

        if (!empty($orderStepData['specs'])) {
            $this->attachSpecificationOptions($order, $orderStepData['specs']);
        }

        $shippingInfo = $orderStepData['shipping_info'] ?? null;
        $pickupInfo = $orderStepData['pickup_info'] ?? null;
        $personalInfo = $orderStepData['personal_info'] ?? [];

        $this->saveOrderAddress($order, $personalInfo, $shippingInfo, $pickupInfo);

        if (!empty($orderStepData['design_info'])) {
            $this->attachDesignToOrder($order, $orderStepData['design_info'], $orderStepData['pricing_details']);
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
        Cache::forget('pickup_contact_' . Auth::id());

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

    private function saveOrderAddress($order, $personalInfo, $shippingInfo = null, $pickupInfo = null)
    {
        if ($shippingInfo) {
            $order->orderAddress()->create([
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

        if ($pickupInfo) {
            $pickupContactData = Cache::get('pickup_contact_' . Auth::id(), []);

            $order->pickupContact()->create([
                'first_name' => $pickupContactData['first_name'] ?? null,
                'last_name' => $pickupContactData['last_name'] ?? null,
                'email' => $pickupContactData['email'] ?? null,
                'phone' => $pickupContactData['phone'] ?? null,
            ]);

            $order->orderAddress()->create([
                'order_id' => $order->id,
                'type' => 'pickup',
                'location_id' => $pickupInfo['id'] ?? null,
                'location_name' => $pickupInfo['location_name'] ?? null,
                'address_label' => $pickupInfo['location_name'] ?? null,
                'address_line' => $pickupInfo['line'] ?? null,
                'state' => $pickupInfo['state'] ?? null,
                'country' => $pickupInfo['country'] ?? null,
                'first_name' => $pickupContactData['first_name'] ?? null,
                'last_name' => $pickupContactData['last_name'] ?? null,
                'email' => $pickupContactData['email'] ?? null,
                'phone' => $pickupContactData['phone'] ?? null,
            ]);
        }
    }

    private function attachDesignToOrder($order, $designInfo, $pricingDetails)
    {
        $design = Design::with(['product', 'productPrice'])->find($designInfo['id']);

        if (!$design) {
            return;
        }

        $quantity = $pricingDetails['quantity'] ?? 1;

        if ($design->product_price_id && $design->productPrice) {
            $customProductPrice = $design->productPrice->price;
            $basePrice = 0;
            $totalPrice = $customProductPrice * $quantity;
        } else {
            $customProductPrice = 0;
            $basePrice = $design->product ? $design->product->base_price : 0;
            $totalPrice = $basePrice * $quantity;
        }

        $pivotData = [
            $design->id => [
                'quantity' => $quantity,
                'base_price' => $basePrice,
                'custom_product_price' => $customProductPrice,
                'total_price' => $totalPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        $order->orderItems()->attach($pivotData);
    }

    public function deleteDesignFromOrder($orderId, $designId)
    {
        return DB::transaction(function () use ($orderId, $designId) {
            $order = Order::with(['orderItems'])->findOrFail($orderId);

            $orderItem = $order->orderItems()->where('design_id', $designId)->first();

            if (!$orderItem) {
                throw new Exception('Design not found in this order.');
            }

            $orderItem->delete();

            $subtotal = $order->orderItems()->sum(DB::raw('quantity * base_price'));
            $totalPrice = $subtotal + $order->delivery_amount + $order->tax_amount - $order->discount_amount;

            $order->update([
                'subtotal' => $subtotal,
                'total_price' => $totalPrice
            ]);

            return $order->refresh();
        });
    }

    public function updateResource($validatedData, $id, $relationsToLoad = [])
    {
        $model = $this->repository->update($validatedData, $id);

        if (
            isset($validatedData['first_name']) ||
            isset($validatedData['last_name']) ||
            isset($validatedData['email']) ||
            isset($validatedData['phone'])
        ) {
            $orderAddress = $model->orderAddress()->first();
            if ($orderAddress) {
                $orderAddress->update([
                    'first_name' => $validatedData['first_name'] ?? $orderAddress->first_name,
                    'last_name' => $validatedData['last_name'] ?? $orderAddress->last_name,
                    'email' => $validatedData['email'] ?? $orderAddress->email,
                    'phone' => $validatedData['phone'] ?? $orderAddress->phone,
                ]);
            }
        }

        if (isset($validatedData['status'])) {
            $model->status = $validatedData['status'];
            $model->save();
        }

        return $model->load($relationsToLoad);
    }


    public function editShippingAddresses($validatedData, $id, $relationsToLoad = [])
    {

        $model = $this->repository->find($id);

        if (!$model) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Order not found');
        }

        // Convert incoming type to enum safely
        $type = $validatedData['type'] instanceof OrderTypeEnum
            ? $validatedData['type']
            : OrderTypeEnum::from((int)$validatedData['type']); // ensures 1 => SHIPPING, 2 => PICKUP

        /** -------------------- SHIPPING -------------------- */
        if ($type === OrderTypeEnum::SHIPPING && !empty($validatedData['shipping_address_id'])) {
            $newAddressId = $validatedData['shipping_address_id'];
            $shippingAddress = ShippingAddress::with(['state.country'])->findOrFail($newAddressId);

            $orderAddress = $model->orderAddress()->where('type', OrderTypeEnum::SHIPPING)->first();

            $addressData = [
                'shipping_address_id' => $newAddressId,
                'address_label'       => $shippingAddress->label,
                'address_line'        => $shippingAddress->line,
                'state'               => optional($shippingAddress->state)->name,
                'country'             => optional($shippingAddress->state?->country)->name,
            ];

            $orderAddress
                ? $orderAddress->update($addressData)
                : $model->orderAddress()->create([
                'order_id' => $model->id,
                'type'     => OrderTypeEnum::SHIPPING,
                ...$addressData
            ]);

            // Remove pickup info if switching to shipping
            $model->orderAddress()->where('type', OrderTypeEnum::PICKUP)->delete();
            $model->pickupContact()?->delete();
        }

        /** -------------------- PICKUP -------------------- */
        if ($type === OrderTypeEnum::PICKUP && !empty($validatedData['location_id'])) {
            $location = Location::with(['state', 'state.country'])->findOrFail($validatedData['location_id']);

            $pickupAddress = $model->orderAddress()->where('type', OrderTypeEnum::PICKUP)->first();

            $pickupAddressData = [
                'location_id'    => $location->id,
                'location_name'  => $location->name,
                'address_label'  => $location->name,
                'address_line'   => $location->address_line,
                'state'          => $location->state?->name ?? 'Unknown',
                'country'        => $location->state?->country?->name ?? $location->country ?? 'Unknown',
            ];

            $pickupAddress
                ? $pickupAddress->update($pickupAddressData)
                : $model->orderAddress()->create([
                'order_id' => $model->id,
                'type'     => OrderTypeEnum::PICKUP,
                ...$pickupAddressData
            ]);

            // Handle pickup contact
            $pickupContactData = array_filter([
                'first_name' => $validatedData['pickup_first_name'] ?? null,
                'last_name'  => $validatedData['pickup_last_name'] ?? null,
                'email'      => $validatedData['pickup_email'] ?? null,
                'phone'      => $validatedData['pickup_phone'] ?? null,
            ]);

            $pickupContact = $model->pickupContact;

            if ($pickupContact) {
                if (!empty($pickupContactData)) {
                    $pickupContact->update($pickupContactData);
                }
            } elseif (!empty($pickupContactData)) {
                $model->pickupContact()->create([
                    'order_id' => $model->id,
                    ...$pickupContactData
                ]);
            }

            // Remove shipping info if switching to pickup
            $model->orderAddress()->where('type', OrderTypeEnum::SHIPPING)->delete();
        }

        return $model->fresh()->load($relationsToLoad ?: ['orderAddress', 'pickupContact']);
    }

    public function downloadPDF(): Response
    {
        $orderData = Cache::get(getOrderStepCacheKey()) ?? [];
        $pdf = Pdf::loadView('dashboard.orders.pdf', compact('orderData'));
        return $pdf->download('order-confirmation.pdf');
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

    public function checkout($request)
    {
        $cart = $this->cartService->getCurrentUserOrGuestCart();
        if (!$cart || $cart->items->isEmpty()) {
            return false;
        }
        $discountCode = $cart->discountCode;
        if ($cart->discountCode()->isNotValid()->exists()) {
            throw ValidationException::withMessages([
                'discountCode' => ['This discount code is not valid.'],
            ]);
        }

        $subTotal = $cart->items()->sum('sub_total');
        $order = $this->handleTransaction(function () use ($cart, $discountCode, $subTotal, $request) {
            $order = $this->repository->query()->create(OrderData::fromCart($subTotal, $discountCode));
            $orderItems = $order->orderItems()->createMany($cart->items->toArray());
            $orderItems->each(function ($item) use ($cart) {
                $cart->items->each(function ($cartItem) use ($item) {
                    $cartItem->specs->each(function ($spec) use ($item) {
                        $item->specs()->create([
                            'spec_name' => $spec->productSpecification?->name,
                            'option_name' => $spec->productSpecificationOption?->value,
                            'option_price' => $spec->productSpecificationOption?->price,
                            'product_specification_id' => $spec->id,
                            'spec_option_id' => $spec->productSpecificationOption->id,
                        ]);
                    });

                });

            });
            $order->orderAddress()->create(OrderAddressData::fromRequest($request));
            if (OrderTypeEnum::from($request->type) == OrderTypeEnum::PICKUP) {
                $order->pickupContact()->create(PickupContactData::fromRequest($request));
            }
            return $order;
        });
        if ($request->payment_method_id) {
            $selectedPaymentMethod = $this->paymentMethodRepository->find($request->payment_method_id);
            $paymentGatewayStrategy = $this->paymentFactory->make($selectedPaymentMethod->paymentGateway->code ?? 'paymob');
            $dto = PaymentRequestData::fromArray(['order' => $order,
                'user' => auth('sanctum')->user(),
                'guest' => $order->orderAddress ?? $order->pickupContact,
                'method' => $selectedPaymentMethod]);
            $paymentDetails = $paymentGatewayStrategy->pay($dto->toArray(), [
                'order' => $order, 'user' => auth('sanctum')->user(),
                'cart' => $cart, 'discountCode' => $discountCode]);
            return [
                'order' => ['id' => $order->id, 'number' => $order->order_number],
                'paymentDetails' => $paymentDetails,
            ];
        }
        return [
            'order' => ['id' => $order->id, 'order_number' => $order->order_number],
            'paymentDetails' => []
        ];

    }

    public function trackOrder($id)
    {
        return $this->repository->find($id);
    }

    public function orderStatuses(): array
    {
        return StatusEnum::toArray();
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
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($pivotData)) {
            $order->orderItems()->attach($pivotData);
        }
    }


}
