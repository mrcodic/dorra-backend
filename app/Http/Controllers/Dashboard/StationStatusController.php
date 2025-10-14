<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\StationStatusService;
use App\Http\Requests\StationStatus\{StoreStationStatusRequest, UpdateStationStatusRequest};



class StationStatusController extends DashboardController
{
   public function __construct(
       public StationStatusService $stationStatusService,
   )
   {
       parent::__construct($stationStatusService);
       $this->storeRequestClass = new StoreStationStatusRequest();
       $this->updateRequestClass = new UpdateStationStatusRequest();
       $this->indexView = 'station-statuses.index';
       $this->usePagination = true;
       $this->resourceTable = 'station_statuses';
   }

    public function getData()
    {
        return $this->stationStatusService->getData();
   }
}
