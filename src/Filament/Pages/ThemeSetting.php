<?php

namespace Northlab\FilamentThemeManager\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class ThemeSetting extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chip';
    protected static ?string $navigationGroup = 'Appearance';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament-theme-manager::filament.theme-setting';

    protected static ?IconPosition $navigationIconPosition = IconPosition::Before;

    #[On('themeSettingNotify')]
    public function transmitNotify(string $status, string $message): void
    {
        Notification::make()
            ->status($status)
            ->title($message)
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return __('Theme Settings');
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}