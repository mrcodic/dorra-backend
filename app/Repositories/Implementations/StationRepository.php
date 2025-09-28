<?php

namespace App\Repositories\Implementations;

use App\Models\Station;
use App\Repositories\{Base\BaseRepository, Interfaces\StationRepositoryInterface};


class StationRepository extends BaseRepository implements StationRepositoryInterface
{
    public function __construct(Station $station)
    {
        parent::__construct($station);
    }



}
