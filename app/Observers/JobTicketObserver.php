<?php

namespace App\Observers;

use App\Enums\JobTicket\StatusEnum;
use App\Models\JobTicket;
use App\Models\Station;

class JobTicketObserver
{

    public function updating(JobTicket $jobTicket): void
    {
      
        if ($jobTicket->isDirty('station_id') && $jobTicket->isClean('current_status_id'))
        {
            $station = optional($jobTicket->station)
                ?? Station::whereKey($jobTicket->station_id)->first();
            $jobTicket->current_status_id = $station->statuses->first()->id;
        }


    }

}
