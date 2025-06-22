<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\CategoryService;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Location\{StoreLocationRequest, UpdateLocationRequest};


class LocationController extends DashboardController
{
    public function __construct(public LocationService $locationService)
    {
        parent::__construct($locationService);
        $this->storeRequestClass = new StoreLocationRequest();
        $this->updateRequestClass = new UpdateLocationRequest();
        $this->indexView = 'logistics.location';
        $this->usePagination = true;
        $this->resourceTable = 'locations';
    }

    public function getData(): JsonResponse
    {
        return $this->locationService->getData();
    }
    public function dashboard()
    {
        return view("dashboard.logistics.dashboard");
    }

}
