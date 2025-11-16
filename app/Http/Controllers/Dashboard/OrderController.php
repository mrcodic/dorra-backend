<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Location;
use App\Repositories\Interfaces\InventoryRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\OrderService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Base\DashboardController;

use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Repositories\Implementations\LocationRepository;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use App\Http\Requests\Order\{StoreOrderRequest, UpdateOrderRequest};


class OrderController extends DashboardController
{
    public function __construct(
        public OrderService                 $orderService,
        public CategoryRepositoryInterface  $categoryRepository,
        public TagRepositoryInterface       $tagRepository,
        public CountryRepositoryInterface   $countryRepository,
        public LocationRepositoryInterface  $LocationRepository,
        public InventoryRepositoryInterface $inventoryRepository,
        public ProductRepositoryInterface   $productRepository,
    )
    {
        parent::__construct($orderService);
        $this->storeRequestClass = new StoreOrderRequest();
        $this->updateRequestClass = new UpdateOrderRequest();
        $this->indexView = 'orders.index';
        $this->createView = 'orders.create';
        $this->editView = 'orders.edit';
        $this->showView = 'orders.show';
        $this->resourceTable = 'orders';
        $this->usePagination = true;
        $this->assoiciatedData = [
            'create' => [
                'categories' => $this->categoryRepository->query(['id', 'name'])
                    ->whereIsHasCategory(1)
                    ->whereNull('parent_id')->get(),
                'products_without' => $this->categoryRepository->query(['id', 'name'])
                    ->whereIsHasCategory(0)
                    ->get(),
                'tags' => $this->tagRepository->all(columns: ['id', 'name']),
                'templates' => [],
            ],
            'edit' => [
                'locations' => $this->LocationRepository->query(['id', 'name', 'address_line', 'latitude', 'longitude'])->get(),
                'inventories' => $this->inventoryRepository->query()->whereNull('parent_id')->get(['id', 'name']),
            ],
            'shared' => [
                'countries' => $this->countryRepository->query(['id', 'name'])->get(),
            ]

        ];

        $this->methodRelations = [
            'edit' => ['orderItems', 'orderItems.product', 'orderItems.itemable'],
            'show' => ['orderItems.itemable', 'orderItems.product'],
        ];

    }


    public function getData()
    {
        return $this->orderService->getData();
    }

    public function storeStep1(Request $request)
    {
        $this->orderService->storeStep1($request);
        return Response::api();
    }

    public function storeStep2(Request $request)
    {
        $this->orderService->storeStep2($request);
        return Response::api();
    }

    public function storeStep4(Request $request)
    {
        $this->orderService->storeStep4($request);
        return Response::api();
    }

    public function storeStep5(Request $request)
    {
        $this->orderService->storeStep5($request);
        return Response::api();
    }

    public function storeStep6(Request $request)
    {
        $this->orderService->storeStep6($request);
        return Response::api();
    }

    public function templateCustomizations(StoreDesignFinalizationRequest $request)
    {
        $this->orderService->templateCustomizations($request);
        return Response::api();
    }

    public function applyDiscountCode(Request $request)
    {
        $orderStepData = Cache::get(getOrderStepCacheKey());
        $code = $this->orderService->applyDiscountCode($request);
        return Response::api(
            message: "discount code applied successfully",
            data: [
                "discount_amount" => getDiscountAmount($code, $orderStepData['pricing_details']['sub_total']),
                "total" => getTotalPrice($code, $orderStepData['pricing_details']['sub_total'], $orderStepData['pricing_details']['delivery']),
            ]
        );
    }


    public function store(Request $request)
    {
        $order = $this->orderService->storeResource([]);
        return Response::api(
            message: 'Order placed successfully!',
            data: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.index', $order->id)
            ]
        );
    }


    public function editShippingAddresses(Request $request, $orderId)
    {

        $validatedData = $request->validate([
            'shipping_address_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'type' => 'nullable|string',
            'pickup_first_name' => 'nullable|string|max:255',
            'pickup_last_name' => 'nullable|string|max:255',
            'pickup_email' => 'nullable|email',
            'pickup_phone' => 'nullable|string',
        ]);

        $order = $this->orderService->editShippingAddresses($validatedData, $orderId);;


        return Response::api(
            message: 'Shipping address updated successfully!',
            data: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect_url' => route('orders.index', $order->id),
            ]
        );
    }

    public function deleteDesign(Request $request, $orderId, $designId)
    {
        try {
            $order = $this->orderService->deleteDesignFromOrder($orderId, $designId);

            return Response::api(
                message: 'Design deleted and order updated successfully!',
                data: [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'redirect_url' => route('orders.index', $order->id),
                ]
            );

        } catch (Exception $e) {
            return Response::api(
                message: $e->getMessage(),
                data: null,
                status: 400
            );
        }
    }


    public function downloadPDF()
    {
        return $this->orderService->downloadPDF();
    }


    public function search(Request $request)
    {
        $query = $request->input('query');
        $location = Location::where('name', 'LIKE', '%' . $query . '%')->first();
        if ($location) {
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'address' => $location->address_line,
                ]
            ]);
        }
        return response()->json(['success' => false, 'message' => 'Location not found.']);
    }

    public function printNewOrders(): JsonResponse
    {
        $html = $this->orderService->printNewOrders();
        return response()->json(['html' => $html]);
    }

    public function print($order): JsonResponse
    {
        $html = $this->orderService->print($order);
        return response()->json(['html' => $html]);
    }


}
