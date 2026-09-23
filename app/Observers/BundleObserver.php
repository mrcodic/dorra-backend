<?php

namespace App\Observers;

use App\Models\Bundle;
use App\Observers\Traits\GeneratesUniqueSlug;

class BundleObserver
{
    use GeneratesUniqueSlug;

    public function creating(Bundle $bundle)
    {
        $this->assignSlugOnCreate($bundle);
    }

    public function updating(Bundle $bundle)
    {
        $this->assignSlugOnUpdate($bundle);
    }

}
