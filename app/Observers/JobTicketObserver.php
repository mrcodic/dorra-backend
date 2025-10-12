<?php

namespace App\Observers;

use App\Enums\JobTicket\StatusEnum;
use App\Models\JobTicket;
use App\Models\Order;
use App\Models\Station;
use App\Models\StationStatus;
use Illuminate\Support\Facades\Log;

class JobTicketObserver
{
    public function creating(JobTicket $jobTicket)
    {
        if (!$jobTicket->station_id) {
            $jobTicket->station_id = Station::first()?->id;
        }

        if (!$jobTicket->current_status_id) {
            $jobTicket->current_status_id = StationStatus::first()?->id;
        }
    }

    public function updating(JobTicket $jobTicket): void
    {
        if ($jobTicket->isDirty('station_id') && $jobTicket->isClean('current_status_id')) {
            $station = optional($jobTicket->station)
                ?? Station::whereKey($jobTicket->station_id)->first();
            $jobTicket->current_status_id = $station->statuses->first()->id;
        }


    }

    public function updated(JobTicket $jobTicket): void
    {
        if (!$jobTicket->wasChanged('current_status_id')) {
            return;
        }
        $orderId = $jobTicket->orderItem->order_id ?? null;
        if (!$orderId) {
            return;
        }

        $terminalStatusIds = $this->workflowTerminalStatusIds();
        if ($terminalStatusIds->isEmpty()) {
            return;
        }


        $hasRemaining = JobTicket::query()
            ->whereHas('orderItem', fn($q) => $q->where('order_id', $orderId))
            ->whereNotIn('current_status_id', $terminalStatusIds)
            ->exists();

        if (!$hasRemaining) {
            Log::info("here");
            Order::whereKey($orderId)->update(['status' => \App\Enums\Order\StatusEnum::PREPARED]);
        }
    }


    protected function workflowTerminalStatusIds()
    {
        return StationStatus::query()
            ->where('is_workflow_terminal', true)
            ->pluck('id');
    }




}
