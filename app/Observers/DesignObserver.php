<?php

namespace App\Observers;

use App\Models\Design;
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

            $designVersion = $design->versions()->create([
                'design_data' => $design->design_data,
                'version'     => $design->current_version,
            ]);

            // Enable query log
            DB::enableQueryLog();

            // Run the actual query
            $firstMedia = $design->media()
                ->where('collection_name', 'designs')
                ->orderBy('order_column') // Optional, used internally by Spatie
                ->first();

            // Get the logged SQL queries
            $queries = DB::getQueryLog();

            // Show SQL + Bindings + Result
            dd([
                'sql'      => $queries[0]['query'],
                'bindings' => $queries[0]['bindings'],
                'result'   => $firstMedia,
            ]);

            if ($firstMedia) {
                $firstMedia->copy($designVersion, 'design-versions');
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
