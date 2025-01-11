<?php

namespace Northlab\FilamentThemeManager;

require_once('helpers.php');

use Filament\PluginServiceProvider;
use Filament\Support\Assets\Asset;
use Filament\Panel;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Northlab\FilamentThemeManager\Models\Theme;
use Northlab\FilamentThemeManager\Enum\AssetCompilerEnum;
use Northlab\FilamentThemeManager\Observers\ThemeObserver;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource;

class FilamentThemeManagerServiceProvider extends PluginServiceProvider
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
        $this->app->resolving(Panel::class, function (Panel $panel) {
            $panel
                ->plugin(new class implements \Filament\Contracts\Plugin {
                    public function getId(): string
                    {
                        return 'filament-themes-manager';
                    }

                    public function register(Panel $panel): void
                    {
                        $panel
                            ->resources([
                                config('filament-themes-manager.theme_resource', ThemeResource::class),
                            ])
                            ->pages([
                                ThemeSetting::class,
                            ]);
                    }

                    public function boot(Panel $panel): void
                    {
                        $theme = get_active_theme();

                        if ($theme?->meta['apply_on_admin'] ?? false) {
                            $themeStylePath = config('filament-theme-manager.theme_style', 'css/filament.css');

                            if ($theme->asset_compiler === AssetCompilerEnum::MIX()->value) {
                                $panel->theme(theme_asset($themeStylePath));
                            }

                            if ($theme->asset_compiler === AssetCompilerEnum::VITE()->value) {
                                $panel->viteTheme(theme_asset($themeStylePath));
                            }
                        }
                    }
                });
        });
    }
}