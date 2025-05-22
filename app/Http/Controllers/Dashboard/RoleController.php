<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Http\Requests\Role\{StoreRoleRequest, UpdateRoleRequest};
use App\Services\RoleService;

class RoleController extends DashboardController
{
    public function __construct(
        public PermissionRepositoryInterface $permissionRepository,
        public RoleRepositoryInterface       $roleRepository,
        RoleService                          $roleService,
    )
    {
        parent::__construct($roleService);
        $this->storeRequestClass = new StoreRoleRequest();
        $this->updateRequestClass = new UpdateRoleRequest();
        $this->indexView = 'roles.index';
        $this->createView = 'roles.create';
        $this->editView = 'roles.edit';
        $this->showView = 'roles.show';
        $this->usePagination = true;
        $this->assoiciatedData = [
            'create' => [
                'permissions' => $this->permissionRepository->query()->get()->groupBy('group'),
            ],
            'index' => [
                'roles' => $this->roleRepository->all(columns: ['id', 'name']),
            ],
        ];
        $this->methodRelations = [
          'edit' => ['permissions',]
        ];
    }


}
