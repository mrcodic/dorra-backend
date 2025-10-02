<?php

namespace App\Repositories\Implementations;


use App\Models\StationStatus;
use App\Repositories\{Base\BaseRepository,
    Interfaces\StationStatusRepositoryInterface};


class StationStatusRepository extends BaseRepository implements StationStatusRepositoryInterface
{
    public function __construct(StationStatus $station)
    {
        parent::__construct($station);
    }



}
