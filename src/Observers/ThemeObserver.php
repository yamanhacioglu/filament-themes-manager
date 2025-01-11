<?php

namespace Northlab\FilamentThemeManager\Observers;

use Northlab\FilamentThemeManager\Models\Theme;
use Northlab\FilamentThemeManager\Jobs\PreparingCloneJob;

class ThemeObserver
{
    /**
     * Handle the Theme "created" event.
     */
    public function created(Theme $theme): void
    {
        if(isset($theme->meta['deploy_after_created']) && $theme->meta['deploy_after_created']) {
            PreparingCloneJob::dispatch($theme);
        }
    }

    /**
     * Handle the Theme "updated" event.
     */
    public function updated(Theme $theme): void
    {
        //
    }

    /**
     * Handle the Theme "deleted" event.
     */
    public function deleted(Theme $theme): void
    {
        //
    }

    /**
     * Handle the Theme "restored" event.
     */
    public function restored(Theme $theme): void
    {
        //
    }

    /**
     * Handle the Theme "force deleted" event.
     */
    public function forceDeleted(Theme $theme): void
    {
        //
    }
}