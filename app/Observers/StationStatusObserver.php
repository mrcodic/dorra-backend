<?php

namespace App\Observers;


use App\Models\JobTicket;
use App\Models\StationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StationStatusObserver
{
    public function creating(StationStatus $stationStatus)
    {
        $stationStatus->code = Str::snake($stationStatus->name);

        DB::transaction(function () use ($stationStatus) {
            $scope = StationStatus::query()
                ->lockForUpdate();

            $last = (clone $scope)->orderByDesc('sequence')->first();

            if ($last) {
                StationStatus::withoutEvents(function () use ($last) {
                    $last->updateQuietly([
                        'is_terminal'           => 0,
                        'is_workflow_terminal'  => 0,
                    ]);
                });
            }

            $stationStatus->sequence             = ($last?->sequence ?? 0) + 1;
            $stationStatus->is_terminal          = 1;
            $stationStatus->is_workflow_terminal = 1;
        });

    }

}
