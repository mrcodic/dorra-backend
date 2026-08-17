<?php

namespace App\Observers;
use App\Models\Design;
use App\Models\Mockup;

class MockupObserver
{
    public function deleted(Mockup $mockup): void
    {
        $templateIds = $mockup
            ->templates()
            ->pluck(
                'templates.id'
            );

        Design::query()
            ->whereIn(
                'template_id',
                $templateIds
            )
            ->lazyById(100)
            ->each(function ($design) {
                $design->clearMediaCollection();

                $design->forceDelete();
            });

        $mockup->clearMediaCollection();
    }
}
