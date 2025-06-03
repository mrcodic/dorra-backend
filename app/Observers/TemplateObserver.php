<?php

namespace App\Observers;

use App\Models\Template;

class TemplateObserver
{
    /**
     * Handle the Template "created" event.
     */
    public function created(Template $template): void
    {
        //
    }

    /**
     * Handle the Template "updated" event.
     */
    public function updated(Template $template): void
    {
    }

    /**
     * Handle the Template "deleted" event.
     */
    public function deleted(Template $template): void
    {
        //
    }

    /**
     * Handle the Template "restored" event.
     */
    public function restored(Template $template): void
    {
        //
    }

    /**
     * Handle the Template "force deleted" event.
     */
    public function forceDeleted(Template $template): void
    {
        //
    }
}
