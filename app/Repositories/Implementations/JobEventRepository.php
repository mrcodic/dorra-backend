<?php

namespace App\Repositories\Implementations;

use App\Models\JobEvent;
use App\Models\JobTicket;
use App\Repositories\{Base\BaseRepository, Interfaces\JobEventRepositoryInterface};


class JobEventRepository extends BaseRepository implements JobEventRepositoryInterface
{
    public function __construct(JobEvent $jobEvent)
    {
        parent::__construct($jobEvent);
    }
}
