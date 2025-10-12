<?php

namespace App\Http\Controllers\Dashboard;


use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Inventory\StoreInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;


class InventoryController extends DashboardController
{
    public function __construct(public InventoryService    $inventoryService,)
    {
        parent::__construct($inventoryService);
        $this->indexView = 'inventories.index';
        $this->storeRequestClass = new StoreInventoryRequest();
        $this->updateRequestClass = new UpdateInventoryRequest();
        $this->usePagination = true;

    }

    public function getData(): JsonResponse
    {
        return $this->inventoryService->getData();
    }

    public function availablePlaces($id)
    {
        return $this->inventoryService->availablePlaces($id);

    }

}
