<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\PlanRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\CreditOrderService;
use App\Http\Requests\CreditOrder\{StoreCreditOrderRequest, UpdateCreditOrderRequest};


use Illuminate\Http\JsonResponse;


class CreditOrderController extends DashboardController
{

    public function __construct(
        public CreditOrderService      $creditOrderService,
        public UserRepositoryInterface $userRepository,
        public PlanRepositoryInterface $planRepository,
    )
    {
        parent::__construct($creditOrderService);
        $this->storeRequestClass = new StoreCreditOrderRequest();
        $this->updateRequestClass = new UpdateCreditOrderRequest();
        $this->indexView = 'credit-orders.index';
        $this->createView = 'credit-orders.create';
        $this->usePagination = true;

        $this->resourceTable = 'credit_orders';
        $this->assoiciatedData = [
            'index' => [
                'plans' => $this->planRepository->query()->active()->get(columns: ['id', 'name', 'price']),
                'users' => $this->userRepository->all(columns: ['id', 'first_name', 'last_name', 'email']),
            ],

        ];
    }

    public function getData(): JsonResponse
    {
        return $this->creditOrderService->getData();
    }


}
