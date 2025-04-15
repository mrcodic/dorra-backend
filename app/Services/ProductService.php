<?php

namespace App\Services;

use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductService extends BaseService
{

    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);

    }

}
