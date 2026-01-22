<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\PlanService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};



class PlanController extends DashboardController
{
   public function __construct(public PlanService $planService)
   {
       parent::__construct($planService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'plans.index';
       $this->usePagination = true;
       $this->resourceTable = 'plans';
       $this->resourceClass = PlanResource::class;
   }

    public function getData()
    {
        return $this->planService->getData();
   }
}
