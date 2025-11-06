<?php

namespace App\Repositories\Implementations;

use App\Models\Industry;
use App\Repositories\{Base\BaseRepository, Interfaces\IndustryRepositoryInterface};
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndustryRepository extends BaseRepository implements IndustryRepositoryInterface
{
    public function __construct(Industry $industry)
    {
        parent::__construct($industry);
    }



}
