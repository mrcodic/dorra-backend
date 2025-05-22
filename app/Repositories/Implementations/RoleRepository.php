<?php

namespace App\Repositories\Implementations;

use App\Models\Role;
use App\Repositories\{Base\BaseRepository, Interfaces\RoleRepositoryInterface};

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

}
