<?php

namespace App\Repositories\Implementations;

use App\Models\Cart;
use App\Models\Country;
use App\Repositories\{Base\BaseRepository,
    Interfaces\CartRepositoryInterface,
};

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
    }

}
