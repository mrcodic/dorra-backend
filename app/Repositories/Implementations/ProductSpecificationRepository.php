<?php

namespace App\Repositories\Implementations;


use App\Models\ProductSpecification;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\ProductSpecificationRepositoryInterface;


class ProductSpecificationRepository extends BaseRepository implements ProductSpecificationRepositoryInterface
{
    public function __construct(ProductSpecification $product)
    {
        parent::__construct($product);
    }



}
