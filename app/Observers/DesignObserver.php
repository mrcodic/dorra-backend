<?php

namespace App\Observers;

use App\Models\Design;

use App\Jobs\CopyDesignMediaJob;

class DesignObserver
{
    /**
     * Handle the Design "created" event.
     */

    public function saved(Design $design): void
    {
        $design->refresh();
        $designVersion = $design->versions()->create([
            'design_data' => $design->design_data,
            'version' => $design->current_version,
        ]);
        CopyDesignMediaJob::dispatch($design, $designVersion);
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
        $designVersion = $design->versions()->create([
            'design_data' => $design->design_data,
            'version' => $design->current_version,
        ]);

        CopyDesignMediaJob::dispatch($design, $designVersion);
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
