<?php

namespace App\Repositories\Implementations;



use App\Models\ProductSpecificationOption;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\ProductSpecificationOptionRepositoryInterface;


class ProductSpecificationOptionRepository extends BaseRepository implements ProductSpecificationOptionRepositoryInterface
{
    public function __construct(ProductSpecificationOption $product)
    {
        parent::__construct($product);
    }



}
