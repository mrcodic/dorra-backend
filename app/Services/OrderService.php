<?php

namespace App\Services;

use App\Repositories\Interfaces\AdminRepositoryInterface;

class OrderService extends BaseService
{

    public function __construct(AdminRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

}
