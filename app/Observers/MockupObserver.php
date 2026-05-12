<?php

namespace App\Observers;

use App\Models\Mockup;

class MockupObserver
{
    public function deleted(Mockup $mockup)
    {

        $mockup->designs->each(function ($design) {
            $design->clearMediaCollections();
            $design->delete();
        });
        $mockup->clearMediaCollections();
    }
}
