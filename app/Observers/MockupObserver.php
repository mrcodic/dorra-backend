<?php

namespace App\Observers;

use App\Models\Design;
use App\Models\Mockup;

class MockupObserver
{
    public function deleted(Mockup $mockup)
    {
        $templateIds = $mockup->templates->pluck('id');
        Design::whereIn('template_id', $templateIds)
            ->get()
            ->each(function ($design) {
                $design->clearMediaCollection();
                $design->delete();
            });
        $mockup->clearMediaCollection();
    }

}
