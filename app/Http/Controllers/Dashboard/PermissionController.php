<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\PermissionService;
use App\Http\Requests\Permission\{StorePermissionRequest, UpdatePermissionRequest};


class PermissionController extends DashboardController
{
   public function __construct(public PermissionService $permissionService)
   {
       parent::__construct($permissionService);
       $this->storeRequestClass = new StorePermissionRequest();
       $this->updateRequestClass = new UpdatePermissionRequest();
       $this->indexView = 'permissions.index';
       $this->usePagination = true;
   }

    public function getData()
    {
        return $this->permissionService->getData();
   }
}
