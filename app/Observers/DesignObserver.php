<?php

namespace App\Observers;

use App\Models\Design;
use App\Models\DesignVersion;

class DesignObserver
{
    /**
     * Handle the Design "created" event.
     */
    public function created(Design $design): void
    {
        //
    }

    /**
     * Handle the Design "updating" event.
     */
    public function updating(Design $design): void
    {
        $design->increment('current_version');
    }
    /**
     * Handle the Design "updated" event.
     */
    public function updated(Design $design): void
    {
        $design->versions()->create([
            'design_data' => $design->design_data,
            'design_image'=> $design->design_image,
            'version'=> $design->current_version,
        ]);
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
