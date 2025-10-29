<?php

namespace App\Repositories\Implementations;

use App\Models\Industry;
use App\Repositories\{Base\BaseRepository, Interfaces\IndustryRepositoryInterface};


class IndustryRepository extends BaseRepository implements IndustryRepositoryInterface
{
    public function __construct(Industry $industry)
    {
        parent::__construct($industry);
    }



}
