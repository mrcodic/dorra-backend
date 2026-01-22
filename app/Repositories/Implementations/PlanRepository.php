<?php

namespace App\Repositories\Implementations;

use App\Models\Plan;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Interfaces\PlanRepositoryInterface;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public function __construct(Plan $plan)
    {
        parent::__construct($plan);
    }
}
