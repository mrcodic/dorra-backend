<?php

namespace App\Services;


use App\Repositories\Interfaces\DesignRepositoryInterface;


class DesignService extends BaseService
{
    public function __construct(DesignRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }


}
