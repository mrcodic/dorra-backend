<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Design\StoreDesignFinalizationRequest;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\CountryRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\OrderService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Order\{StoreOrderRequest, UpdateOrderRequest};


class OrderController extends DashboardController
{
    public function __construct(
        public OrderService $orderService,
        public CategoryRepositoryInterface $categoryRepository,
        public TagRepositoryInterface $tagRepository,
        public CountryRepositoryInterface $countryRepository,
    ) {
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
                'countries' => $this->countryRepository->query(['id', 'name'])->get(),
                'categories' => $this->categoryRepository->query(['id', 'name'])->whereNull('parent_id')->get(),
                'tags' => $this->tagRepository->all(columns: ['id', 'name']),
                'templates' => [],
            ],
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
                "discount_amount" => getDiscountAmount($code->value, $orderStepData['pricing_details']['sub_total']),
                "total" => getTotalPrice($code->value, $orderStepData['pricing_details']['sub_total']),
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



    public function downloadPDF()
    {
        return $this->orderService->downloadPDF();
    }

}
