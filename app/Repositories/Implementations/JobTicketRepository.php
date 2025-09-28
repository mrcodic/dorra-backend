<?php

namespace App\Repositories\Implementations;

use App\Models\JobTicket;
use App\Repositories\{Base\BaseRepository, Interfaces\JobTicketRepositoryInterface};


class JobTicketRepository extends BaseRepository implements JobTicketRepositoryInterface
{
    public function __construct(JobTicket $jobTicket)
    {
        parent::__construct($jobTicket);
    }
}
