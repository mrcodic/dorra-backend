<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Base\DashboardController;
use App\Http\Resources\RoleResource;
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
        $this->resourceClass = RoleResource::class;
        $this->assoiciatedData = [
            'shared' => [
                'permissions' => $this->permissionRepository->query()->get()->groupBy('group_key'),
            ],
            'index' => [
                'roles' => $this->roleRepository->query()
                    ->withCount('users')
                    ->with(['users.media','users'])
                    ->get(columns: ['id', 'name']),
            ],
        ];
        $this->methodRelations = [
          'edit' => ['permissions',]
        ];
    }




}
