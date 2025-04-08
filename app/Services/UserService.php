<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;

class UserService extends BaseService
{

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

}
