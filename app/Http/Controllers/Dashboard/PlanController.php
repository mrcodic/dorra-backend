<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\AdminResource;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Services\AdminService;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};



class PlanController extends DashboardController
{
   public function __construct(public AdminService $adminService, public RoleRepositoryInterface $roleRepository)
   {
       parent::__construct($adminService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'admins.index';
       $this->usePagination = true;
       $this->assoiciatedData = [
           'index' => [
               'roles' => $this->roleRepository->all(),
           ]
       ];
       $this->resourceTable = 'admins';
       $this->resourceClass = AdminResource::class;
   }

    public function getData()
    {
        return $this->adminService->getData();
   }
}
