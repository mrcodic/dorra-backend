<?php

namespace App\Repositories\Implementations;

use App\Models\Carousel;
use App\Models\CreditOrder;
use App\Repositories\{Base\BaseRepository, Interfaces\CreditOrderRepositoryInterface,};

class CreditOrderRepository extends BaseRepository implements CreditOrderRepositoryInterface
{
    public function __construct(CreditOrder $order)
    {
        parent::__construct($order);
    }

}
