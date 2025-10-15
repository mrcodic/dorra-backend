<?php

namespace App\Observers;


use App\Models\StationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StationStatusObserver
{
    public function creating(StationStatus $stationStatus)
    {
        $stationStatus->code = Str::snake($stationStatus->name);
        $stationStatus->sequence = DB::transaction(function () use ($stationStatus) {
            $q = StationStatus::query();

            if (is_null($stationStatus->parent_id)) {
                $q->whereNull('parent_id');
            } else {
                $q->where('parent_id', $stationStatus->parent_id);
            }

            $max = $q->lockForUpdate()->max('sequence');

            return (int) ($max ?? 0) + 1;
        });


    }
}
