<?php

namespace App\Observers;

use App\Models\Category;

class CategoryObserver
{
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
