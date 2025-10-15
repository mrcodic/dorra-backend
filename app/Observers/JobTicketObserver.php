<?php

namespace App\Observers;

use App\Enums\JobTicket\StatusEnum;
use App\Models\JobTicket;
use App\Models\Order;
use App\Models\Station;
use App\Models\StationStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobTicketObserver
{
    public function creating(JobTicket $jobTicket)
    {

        $startStation = Station::query()
            ->orderBy('workflow_order')
            ->first() ?? Station::query()->first();

        $jobTicket->station_id = $startStation?->id;


        $currentStatusId = null;

        if ($startStation && $jobTicket->orderItem && $jobTicket->orderItem->orderable) {
            $orderable = $jobTicket->orderItem->orderable;

            if (method_exists($orderable, 'stationStatuses')) {
                $customQ = $orderable->stationStatuses()->whereBelongsTo($startStation);

                if ($customQ->exists()) {
                    $currentStatusId = (int) $customQ->orderBy('sequence')->limit(1)->value('id');
                }
            }
        }

        if (!$currentStatusId && $startStation) {
            $currentStatusId = (int) $startStation->statuses()->orderBy('sequence')->limit(1)->value('id');
        }

        $jobTicket->current_status_id = $currentStatusId ?: null;
    }


    public function updating(JobTicket $jobTicket): void
    {
        if ($jobTicket->isDirty('station_id') && $jobTicket->isClean('current_status_id')) {
            $station = optional($jobTicket->station)
                ?? Station::whereKey($jobTicket->station_id)->first();
            $jobTicket->current_status_id = $jobTicket->orderItem->orderable->stationStatuses->isEmpty() ?
                $station->statuses->first()->id
                : $jobTicket->orderItem->orderable->stationStatuses->first()?->id;

        }
    }


    public function updated(JobTicket $jobTicket): void
    {
        if (!$jobTicket->wasChanged('current_status_id')) {
            return;
        }
        $terminalStatusIds = $this->workflowTerminalStatusIds();
        if ($terminalStatusIds->isEmpty()) return;

        if (!in_array($jobTicket->current_status_id, $terminalStatusIds->all(), true)) {
            return;
        }

        $orderId = $jobTicket->orderItem()->value('order_id'); // no relation access needed
        if (!$orderId) return;

        DB::transaction(function () use ($orderId, $terminalStatusIds) {
            Order::whereKey($orderId)->lockForUpdate()->first();
            $hasRemaining = JobTicket::query()
                ->whereHas('orderItem', fn($q) => $q->where('order_id', $orderId))
                ->whereNotIn('current_status_id', $terminalStatusIds)
                ->exists();

            if (!$hasRemaining) {
                Order::whereKey($orderId)
                    ->first()
                    ->update(['status' => \App\Enums\Order\StatusEnum::PREPARED]);
            }
        });
    }


    protected function workflowTerminalStatusIds()
    {
        return StationStatus::query()
            ->where('is_workflow_terminal', true)
            ->pluck('id');
    }


}
