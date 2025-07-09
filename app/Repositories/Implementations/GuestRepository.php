<?php

namespace App\Repositories\Implementations;

use App\Models\Guest;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\GuestRepositoryInterface;

class GuestRepository extends BaseRepository implements GuestRepositoryInterface
{
    public function __construct(Guest $guest)
    {
        parent::__construct($guest);
    }

}
