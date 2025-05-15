<?php

namespace App\Repositories\Implementations;

use App\Repositories\{Base\BaseRepository,
    Interfaces\PermissionRepositoryInterface,};
use App\Models\Permission;


class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $permission)
    {
        parent::__construct($permission);
    }

}
