<?php

namespace App\Repositories\Implementations;

use App\Models\Dimension;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\DimensionRepositoryInterface;

class DimensionRepository extends BaseRepository implements DimensionRepositoryInterface
{
    public function __construct(Dimension $dimension)
    {
        parent::__construct($dimension);
    }

}
