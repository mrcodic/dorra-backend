<?php

namespace App\Observers;

use App\Models\Category;
use App\Observers\Traits\GeneratesUniqueSlug;

class CategoryObserver
{
    use GeneratesUniqueSlug;

    public function creating(Category $category)
    {
        $this->assignSlugOnCreate($category);
    }

    public function updating(Category $category)
    {
        $this->assignSlugOnUpdate($category);
    }

    public function created(Category $category)
    {
        cache()->forget('menu_categories');
    }

    public function updated(Category $category)
    {
        cache()->forget('menu_categories');
    }

    public function deleted(Category $category)
    {
        cache()->forget('menu_categories');
    }
}
