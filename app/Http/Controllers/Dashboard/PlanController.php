<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\PlanResource;
use App\Services\PlanService;
use App\Http\Requests\Plan\{StorePlanRequest, UpdatePlanRequest};



class PlanController extends DashboardController
{
   public function __construct(public PlanService $planService)
   {
       parent::__construct($planService);
       $this->storeRequestClass = new StorePlanRequest();
       $this->updateRequestClass = new UpdatePlanRequest();
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
