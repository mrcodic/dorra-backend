<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\JobTicketRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
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
       public JobTicketRepositoryInterface $jobTicketRepository,
       public ProductRepositoryInterface              $productRepositoryInterface,
       public CategoryRepositoryInterface              $categoryRepository,
       public ProductRepositoryInterface              $productRepository,

   )
   {
       parent::__construct($stationStatusService);
       $this->storeRequestClass = new StoreStationStatusRequest();
       $this->updateRequestClass = new UpdateStationStatusRequest();
       $this->indexView = 'station-statuses.index';
       $this->usePagination = true;
       $this->resourceTable = 'station_statuses';
       $this->assoiciatedData = [
          'index' => [
              'products' => $this->productRepository->query()->get(['id', 'name']),
              'product_with_categories' => $this->categoryRepository->query()->where('is_has_category',1)->has('products')->get(['id', 'name']),
              'product_without_categories' => $this->categoryRepository->query()->where('is_has_category',0)->get(['id', 'name']),
              'stations' => $this->stationRepository->query(['id','name'])->get(),

          ]
       ];
   }

    public function getData()
    {
        return $this->stationStatusService->getData();
   }
}
