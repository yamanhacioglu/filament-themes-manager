<?php

namespace Northlab\FilamentThemeManager\Filament\Resources\ThemeResource\Pages;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Northlab\FilamentThemeManager\Enum\DeploymentTypeEnum;
use Northlab\FilamentThemeManager\Enum\DeploymentStatusEnum;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource;
use Northlab\FilamentThemeManager\Models\ThemeDeploymentLog;
use Filament\Resources\Pages\ViewRecord;

class ViewDeploymentLog extends ViewRecord
{
    protected static string $resource = ThemeResource::class;

    protected static ?string $title = "Deployment Log";

    protected static string $view = 'filament-theme-manager::filament.resources.theme-resource.pages.view-deployment-log';

    public ?ThemeDeploymentLog $deployment_log = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToTheme')
                ->url(fn () => route('filament.resources.themes.view', ['record' => $this->record])),
        ];
    }

    public function mount($record): void
    {
        parent::mount($record);

        $log = ThemeDeploymentLog::find(request()->get('log_id'));

        if (empty($log)) {
            abort(404);
        }

        $this->deployment_log = $log;

        static::$title = "Log : {$log->name}";
    }
}