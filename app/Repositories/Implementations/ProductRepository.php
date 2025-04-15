<?php

namespace App\Repositories\Implementations;


use App\Models\Product;
use App\Repositories\Base\BaseRepository;

use App\Repositories\Interfaces\ProductRepositoryInterface;


class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }



}
