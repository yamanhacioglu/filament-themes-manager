<?php

namespace Northlab\FilamentThemeManager\Http\Livewire\Form;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\On;

class ThemeSetting extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            "active_theme" => get_theme_setting('active_theme', 0),
            "gitlab_username" => get_theme_setting('gitlab_username'),
            "gitlab_password" => get_theme_setting('gitlab_password'),
            "github_username" => get_theme_setting('github_username'),
            "github_password" => get_theme_setting('github_password'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('active_theme')
                            ->nullable()
                            ->options(get_themes()->pluck('name', 'id')->put(0, 'Default'))
                    ]),

                Grid::make([
                    'default' => 1,
                    'lg' => 4
                ])
                    ->schema([
                        Grid::make(['default' => 1])
                            ->schema([
                                Section::make('Gitlab Account')
                                    ->description('Uses for non SSH auth clone / deploy theme')
                                    ->schema([
                                        TextInput::make('gitlab_username')
                                            ->label('Gitlab Username'),
                                        TextInput::make('gitlab_password')
                                            ->label('Gitlab Password')
                                            ->type('password')
                                    ])
                            ])->columnSpan(2),

                        Grid::make(['default' => 1])
                            ->schema([
                                Section::make('Github Account')
                                    ->description('Uses for non SSH auth clone / deploy theme')
                                    ->schema([
                                        TextInput::make('github_username')
                                            ->label('Github Username'),
                                        TextInput::make('github_password')
                                            ->label('Github Password')
                                            ->type('password')
                                    ])
                            ])->columnSpan(2),
                    ])
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            foreach($data as $key => $value) {
                set_theme_setting($key, $value);
            }

            DB::commit();

            $this->dispatch('themeSettingNotify', [
                'type' => 'success',
                'message' => 'Settings saved!'
            ]);
        } catch(\Exception $e) {
            DB::rollBack();

            $this->dispatch('themeSettingNotify', [
                'type' => 'danger',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('filament-theme-manager::livewire.theme-setting');
    }
}