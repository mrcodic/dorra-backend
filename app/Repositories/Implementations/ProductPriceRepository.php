<?php

namespace App\Repositories\Implementations;



use App\Models\ProductPrice;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\ProductPriceRepositoryInterface;


class ProductPriceRepository extends BaseRepository implements ProductPriceRepositoryInterface
{
    public function __construct(ProductPrice $product)
    {
        parent::__construct($product);
    }



}
