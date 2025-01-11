<?php

namespace Northlab\FilamentThemeManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Northlab\FilamentThemeManager\Filament\Pages\ThemeSetting;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource;
use Northlab\FilamentThemeManager\Enum\AssetCompilerEnum;

class FilamentThemeManagerPlugin implements Plugin
{
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
}