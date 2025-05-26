<?php

namespace App\Repositories\Implementations;


use App\Models\Design;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\DesignRepositoryInterface;


class DesignRepository extends BaseRepository implements DesignRepositoryInterface
{
    public function __construct(Design $design)
    {
        parent::__construct($design);
    }



}
