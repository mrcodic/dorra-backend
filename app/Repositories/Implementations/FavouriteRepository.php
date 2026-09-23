<?php

namespace App\Repositories\Implementations;

use App\Models\Favourite;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\FavouriteRepositoryInterface;

class FavouriteRepository extends BaseRepository implements FavouriteRepositoryInterface
{
    public function __construct(Favourite $favourite)
    {
        parent::__construct($favourite);
    }
}
