<?php

namespace App\Observers;

use App\Models\Mockup;

class MockupObserver
{
    public function deleted(Mockup $mockup)
    {
        $mockup->clearMediaCollections();
        $mockup->designs()->delete();
    }
}
