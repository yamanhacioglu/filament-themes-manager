<?php

namespace Northlab\FilamentThemeManager;

require_once('helpers.php');

use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\ServiceProvider;
use Filament\Panel;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Northlab\FilamentThemeManager\Models\Theme;
use Northlab\FilamentThemeManager\Enum\AssetCompilerEnum;
use Northlab\FilamentThemeManager\Observers\ThemeObserver;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource;
use Northlab\FilamentThemeManager\Filament\Pages\ThemeSetting;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentThemeManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-themes-manager')
            ->hasViews()
            ->hasMigrations([
                'create_themes',
                'create_theme_deployment_logs',
                'create_theme_settings'
            ])
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $themeModel = config('filament-themes-manager.theme_model', Theme::class);
        $themeModel::observe(ThemeObserver::class);

        Livewire::component('theme-setting', \Northlab\FilamentThemeManager\Http\Livewire\Form\ThemeSetting::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('filament-themes-manager', fn() => new FilamentThemeManagerPlugin());

        $this->app->resolving(Panel::class, function (Panel $panel) {
            $panel->plugin(app('filament-themes-manager'));
        });
    }
}