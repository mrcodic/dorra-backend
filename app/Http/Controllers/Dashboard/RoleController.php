<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use App\Services\AdminService;


class RoleController extends DashboardController
{
   public function __construct(AdminService $adminService)
   {
       parent::__construct($adminService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'roles.index';
       $this->createView = 'roles.create';
       $this->editView = 'roles.edit';
       $this->showView = 'roles.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
}
