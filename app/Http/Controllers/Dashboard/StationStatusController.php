<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\JobTicketRepositoryInterface;
use App\Repositories\Interfaces\StationRepositoryInterface;
use App\Repositories\Interfaces\StationStatusRepositoryInterface;
use App\Services\StationStatusService;
use App\Http\Requests\StationStatus\{StoreStationStatusRequest, UpdateStationStatusRequest};



class StationStatusController extends DashboardController
{
   public function __construct(
       public StationStatusService $stationStatusService,
       public StationRepositoryInterface $stationRepository,
       public StationStatusRepositoryInterface $stationStatusRepository,
       public JobTicketRepositoryInterface $jobTicketRepository
   )
   {
       parent::__construct($stationStatusService);
       $this->storeRequestClass = new StoreStationStatusRequest();
       $this->updateRequestClass = new UpdateStationStatusRequest();
       $this->indexView = 'station-statuses.index';
       $this->usePagination = true;
       $this->resourceTable = 'station_statuses';
       $this->assoiciatedData = [
           'stations' => $this->stationRepository->query(['id','name'])->get(),
           'statuses' => $this->stationStatusRepository->query()
               ->whereNull('parent_id')
               ->get(),
           'job_tickets' => $this->jobTicketRepository->query()
           ->get()
       ];
   }

    public function getData()
    {
        return $this->stationStatusService->getData();
   }
}
