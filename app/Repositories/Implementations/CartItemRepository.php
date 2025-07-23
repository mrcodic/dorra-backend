<?php

namespace App\Repositories\Implementations;

use App\Models\CartItem;
use App\Repositories\{Base\BaseRepository,
    Interfaces\CartItemRepositoryInterface,
};

class CartItemRepository extends BaseRepository implements CartItemRepositoryInterface
{
    public function __construct(CartItem $cartItem)
    {
        parent::__construct($cartItem);
    }

}
