<?php

namespace App\Repositories\Implementations;

use App\Repositories\{Base\BaseRepository, Interfaces\RoleRepositoryInterface};
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $role)
    {
        parent::__construct($role);
    }

}
