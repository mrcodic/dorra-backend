<?php

namespace App\Repositories\Implementations;

use App\Models\Order;
use App\Repositories\{Base\BaseRepository,
    Interfaces\OrderRepositoryInterface,
};

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

}
