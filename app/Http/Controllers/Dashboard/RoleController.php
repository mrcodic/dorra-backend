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
                    ->when(config('permission.defaults.guard'), fn($q, $guard) => $q->where('guard_name', $guard))
                    ->withCount('users')
                    ->with([
                        'users' => function ($q) {
                            $q->select('admins.id', 'admins.first_name', 'admins.last_name')
                            ->with('media');
                        },
                    ])
                    ->get(columns: ['id', 'name','guard_name']),
            ],
        ];
        $this->methodRelations = [
          'edit' => ['permissions',]
        ];
    }




}
