<?php

namespace App\Observers;

use App\Enums\JobTicket\StatusEnum;
use App\Models\JobTicket;
use App\Models\Station;
use App\Services\BarcodeService;

class JobTicketObserver
{

    public function updating(JobTicket $jobTicket): void
    {
        if (!$jobTicket->isDirty('station_id')) {
            return;
        }

        $stationCode = optional($jobTicket->station)->code
            ?? Station::whereKey($jobTicket->station_id)->value('code');

        $jobTicket->status = $this->statusForStation($stationCode);
        if (!$jobTicket->station) {
            $svc = app(BarcodeService::class);
            $svc->savePng1D($jobTicket->code, 'C128', scale: 3, height: 80);
            $svc->saveSvg1D($jobTicket->code, 'C128', width: 2, height: 60, withText: true);
        }
    }


    private function statusForStation(?string $code): StatusEnum
    {
        $code = $code ? strtolower($code) : null;

        return match ($code) {
            'prepress' => StatusEnum::PREPRESS_QUEUE,
            'print' => StatusEnum::PRINT_QUEUE,
            'finish' => StatusEnum::FINISH_QUEUE,
            'qc' => StatusEnum::QC_QUEUE,
            'pack' => StatusEnum::PACK_QUEUE,
            default => StatusEnum::PREPRESS_QUEUE,
        };
    }
}
