<?php

namespace App\Repositories\Implementations;

use App\Models\Inventory;
use App\Repositories\{Base\BaseRepository, Interfaces\InventoryRepositoryInterface};


class InventoryRepository extends BaseRepository implements InventoryRepositoryInterface
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);
    }


}
