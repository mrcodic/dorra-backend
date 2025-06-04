<?php

namespace App\Observers;

use App\Models\Design;
use App\Models\DesignVersion;
use Illuminate\Support\Facades\DB;

class DesignObserver
{
    /**
     * Handle the Design "created" event.
     */
    public function created(Design $design): void
    {
        DB::transaction(function () use ($design) {
            $design->refresh();
            $design->versions()->create([
                'design_data' => $design->design_data,
                'version'     => $design->current_version,
            ]);


            $firstMedia = $design->getFirstMedia('designs');
            if ($firstMedia) {
                $firstMedia->copy($design, 'design-versions');
            }
        });
    }

    /**
     * Handle the Design "updating" event.
     */
    public function updating(Design $design): void
    {
        $design->current_version += 1;
    }
    /**
     * Handle the Design "updated" event.
     */
    public function updated(Design $design): void
    {
        DB::transaction(function () use ($design) {
            $design->versions()->create([
                'design_data' => $design->design_data,
                'version'     => $design->current_version,
            ]);


            $firstMedia = $design->getFirstMedia('designs');
            if ($firstMedia) {
                $firstMedia->copy($design, 'design-versions');
            }
        });

    }

    /**
     * Handle the Design "deleted" event.
     */
    public function deleted(Design $design): void
    {
        //
    }

    /**
     * Handle the Design "restored" event.
     */
    public function restored(Design $design): void
    {
        //
    }

    /**
     * Handle the Design "force deleted" event.
     */
    public function forceDeleted(Design $design): void
    {
        //
    }
}
