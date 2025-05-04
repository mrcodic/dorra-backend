<?php

namespace App\Repositories\Implementations;

use App\Repositories\{Base\BaseRepository,
    Interfaces\PermissionRepositoryInterface,
    Interfaces\RoleRepositoryInterface};
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

}
