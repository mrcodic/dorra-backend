<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use App\Services\AdminService;


class PermissionController extends DashboardController
{
   public function __construct(AdminService $adminService)
   {
       parent::__construct($adminService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'permissions.index';
       $this->createView = 'permissions.create';
       $this->editView = 'permissions.edit';
       $this->showView = 'permissions.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
}
