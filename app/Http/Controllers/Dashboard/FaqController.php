<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Requests\Admin\{StoreAdminRequest, UpdateAdminRequest};
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Services\AdminService;


class FaqController extends DashboardController
{
   public function __construct(public AdminService $adminService, public RoleRepositoryInterface $roleRepository)
   {
       parent::__construct($adminService);
       $this->storeRequestClass = new StoreAdminRequest();
       $this->updateRequestClass = new UpdateAdminRequest();
       $this->indexView = 'faqs.index';
       $this->usePagination = true;
       $this->assoiciatedData = [
           'index' => [
               'roles' => $this->roleRepository->all(),
           ]
       ];
       $this->resourceTable = 'faqs';
   }

    public function getData()
    {
        return $this->adminService->getData();
   }
}
