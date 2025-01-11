<?php

namespace Northlab\FilamentThemeManager\Filament\Resources\ThemeResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Northlab\FilamentThemeManager\Jobs\PreparingDeployJob;
use Northlab\FilamentThemeManager\Jobs\PreparingCloneJob;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource;

class ViewTheme extends ViewRecord
{
    protected static string $resource = ThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deploy')
                ->action(fn () => PreparingDeployJob::dispatch($this->record))
                ->requiresConfirmation(),

            Action::make('reClone')
                ->color('secondary')
                ->requiresConfirmation()
                ->action(fn () => PreparingCloneJob::dispatch($this->record))
        ];
    }
}