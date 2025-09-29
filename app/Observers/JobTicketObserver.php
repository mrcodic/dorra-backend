<?php

namespace App\Observers;

use App\Enums\JobTicket\StatusEnum;
use App\Models\JobTicket;
use App\Models\Station;

class JobTicketObserver
{

    public function updating(JobTicket $jobTicket): void
    {
        if (! $jobTicket->isDirty('station_id')) {
            return;
        }

        $stationCode = optional($jobTicket->station)->code
            ?? Station::whereKey($jobTicket->station_id)->value('code');

        $jobTicket->status = $this->statusForStation($stationCode);
        $jobTicket->save();
    }


    private function statusForStation(?string $code): StatusEnum
    {
        $code = $code ? strtolower($code) : null;

        return match ($code) {
            'print'    => StatusEnum::PRINT_QUEUE,
            'finish'   => StatusEnum::FINISH_QUEUE,
            'qc'       => StatusEnum::QC_QUEUE,
            'pack'     => StatusEnum::PACK_QUEUE,
            default    => StatusEnum::PREPRESS_QUEUE,
        };
    }
}
