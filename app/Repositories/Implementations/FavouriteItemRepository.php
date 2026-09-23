<?php

namespace App\Repositories\Implementations;

use App\Models\FavouriteItem;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\FavouriteItemRepositoryInterface;

class FavouriteItemRepository extends BaseRepository implements FavouriteItemRepositoryInterface
{
    public function __construct(FavouriteItem $favouriteItem)
    {
        parent::__construct($favouriteItem);
    }
}
