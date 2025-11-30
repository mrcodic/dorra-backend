<?php

namespace App\Repositories\Implementations;


use App\Models\Position;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\PositionRepositoryInterface;

class PositionRepository extends BaseRepository implements PositionRepositoryInterface
{
    public function __construct(Position $position)
    {
        parent::__construct($position);
    }
}
