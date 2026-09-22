<?php

namespace App\Observers;

use App\Models\Product;
use App\Observers\Traits\GeneratesUniqueSlug;

class ProductObserver
{
    use GeneratesUniqueSlug;

    public function creating(Product $product)
    {
        $this->assignSlugOnCreate($product);
    }

    public function updating(Product $product)
    {
        $this->assignSlugOnUpdate($product);
    }

}
