<?php

namespace App\Services;

use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryService extends BaseService
{

    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

}
