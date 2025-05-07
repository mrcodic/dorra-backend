<?php

namespace App\Repositories\Implementations;

use App\Models\DiscountCode;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\DiscountCodeRepositoryInterface;


class DiscountCodeRepository extends BaseRepository implements DiscountCodeRepositoryInterface
{
    public function __construct(DiscountCode $discountCode)
    {
        parent::__construct($discountCode);
    }



}
