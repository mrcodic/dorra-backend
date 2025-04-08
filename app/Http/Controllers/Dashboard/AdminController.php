<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Services\AdminService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};



class AdminController extends DashboardController
{
   public function __construct(AdminService $adminService)
   {
       parent::__construct($adminService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'admins.index';
       $this->createView = 'admins.create';
       $this->editView = 'admins.edit';
       $this->showView = 'admins.show';
       $this->usePagination = true;
       $this->successMessage = 'Process success';
   }
}
