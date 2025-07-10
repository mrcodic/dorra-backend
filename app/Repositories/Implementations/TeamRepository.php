<?php

namespace App\Repositories\Implementations;

use App\Models\Team;
use App\Repositories\{Base\BaseRepository, Interfaces\TeamRepositoryInterface};


class TeamRepository extends BaseRepository implements TeamRepositoryInterface
{
    public function __construct(Team $team)
    {
        parent::__construct($team);
    }



}
