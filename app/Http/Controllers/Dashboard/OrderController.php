<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\OrderService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use App\Services\AdminService;


class OrderController extends DashboardController
{
   public function __construct(OrderService $orderService)
   {
       parent::__construct($orderService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'orders.index';
       $this->createView = 'orders.create';
       $this->editView = 'orders.edit';
       $this->showView = 'orders.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
}
