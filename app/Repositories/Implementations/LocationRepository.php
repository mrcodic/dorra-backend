<?php

namespace App\Repositories\Implementations;

use App\Models\Location;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\LocationRepositoryInterface;

class LocationRepository extends BaseRepository implements LocationRepositoryInterface
{
    public function __construct(Location $location)
    {
        parent::__construct($location);
    }
}
