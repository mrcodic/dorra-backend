<?php

namespace App\Observers;

use App\Models\Cart;
use App\Models\Design;

use App\Jobs\CopyDesignMediaJob;

class DesignObserver
{
    /**
     * Handle the Design "created" event.
     */
    public function creating(Design $design)
    {
        if ($design->tempalte_id)
        {
            $design->current_version += 1;
        }
    }
    public function created(Design $design): void
    {
        $design->refresh();
        if ($design->tempalte_id)
        {
            $designVersion = $design->versions()->create([
                'design_data' => $design->design_data,
                'design_back_data' => $design->design_back_data,
                'version' => $design->current_version,
            ]);
            CopyDesignMediaJob::dispatch($design, $designVersion);
        }


    }

    /**
     * Handle the Design "updating" event.
     */
    public function updating(Design $design): void
    {
        if ($design->wasChanged('design_data')) {
            $design->current_version += 1;

        }
    }

    /**
     * Handle the Design "updated" event.
     */
    public function updated(Design $design): void
    {
        if ($design->wasChanged('design_data')) {
            $designVersion = $design->versions()->create([
                'design_data' => $design->design_data,
                'version' => $design->current_version,
            ]);

            CopyDesignMediaJob::dispatch($design, $designVersion);
        }

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
