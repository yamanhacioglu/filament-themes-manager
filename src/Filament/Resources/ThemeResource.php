<?php

namespace Northlab\FilamentThemeManager\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\BooleanColumn;
use Northlab\FilamentThemeManager\Enum\GitProviderEnum;
use Northlab\FilamentThemeManager\Enum\AssetCompilerEnum;
use Northlab\FilamentThemeManager\Enum\GitConnectionEnum;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource\Pages;
use Northlab\FilamentThemeManager\Filament\Resources\ThemeResource\RelationManagers;
use Illuminate\Support\Str;

class ThemeResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-collection';
    protected static ?string $navigationGroup = 'Appearance';
    protected static ?int $navigationSort = 1;

    public static function getModel(): string
    {
        return config('filament-theme-manager.theme_model') ?? (string) Str::of(class_basename(static::class))
            ->beforeLast('Resource')
            ->prepend('App\\Models\\');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make([
                    'default' => 1,
                    'lg' => 3
                ])
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1])
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 3
                                        ])->schema([
                                            TextInput::make('name')
                                                ->maxLength(25)
                                                ->required(),
                                            TextInput::make('vendor')
                                                ->maxLength(25)
                                                ->required(),
                                            TextInput::make('directory')
                                                ->maxLength(25)
                                                ->required(),
                                        ]),

                                        Checkbox::make('is_child')
                                            ->reactive()
                                            ->label('Is a Child Theme ?'),

                                        Select::make('parent_id')
                                            ->relationship('parent_theme', 'name')
                                            ->searchable()
                                            ->required()
                                            ->visible(fn (Forms\Get $get) => $get('is_child')),

                                        Grid::make([
                                            'default' => 1,
                                            'lg' => 3
                                        ])->schema([
                                            TextInput::make('git_username')
                                                ->label("Git Username")
                                                ->required(),
                                            TextInput::make('git_repository')
                                                ->label('Repository')
                                                ->required(),
                                            TextInput::make('git_branch')
                                                ->label('Branch')
                                                ->required()
                                        ]),

                                        Select::make('connection_type')
                                            ->reactive()
                                            ->options(
                                                collect(GitConnectionEnum::toValues())->combine(GitConnectionEnum::toLabels())->map(function($item){
                                                    return ucwords(str_replace('_', ' ', strtolower($item)));
                                                })->forget(GitConnectionEnum::HTTPS()->value)
                                            )
                                            ->helperText(
                                                function(Forms\Components\Component $component){
                                                    return $component->getState() == GitConnectionEnum::SSH()->value
                                                        ? 'Make sure your server has whitelist ssh key to connect your repository'
                                                        : null;
                                                }
                                            )
                                            ->required(),

                                        Select::make('asset_compiler')
                                            ->options(
                                                collect(AssetCompilerEnum::toValues())->combine(AssetCompilerEnum::toLabels())
                                            )
                                            ->required(),

                                        Select::make('git_provider')
                                            ->options(
                                                collect(GitProviderEnum::toValues())->combine(GitProviderEnum::toLabels())->map(function($item){
                                                    return ucwords(str_replace('_', ' ', strtolower($item)));
                                                })
                                            )
                                            ->required(),

                                        Checkbox::make('meta.deploy_after_created')
                                            ->visible(fn (Forms\Get $get) => $get('record') instanceof \Filament\Resources\Pages\CreateRecord),

                                        Checkbox::make('meta.apply_on_app')
                                            ->helperText('Theme will applied on user view')
                                            ->default(false),

                                        Checkbox::make('meta.apply_on_admin')
                                            ->helperText('Theme will applied on filament admin')
                                            ->default(false),

                                        TextInput::make('meta.git_password')
                                            ->required()
                                            ->visible(fn (Forms\Get $get) => $get('connection_type') == GitConnectionEnum::HTTPS()->value)
                                    ])
                            ])->columnSpan(2),

                        Forms\Components\Grid::make(['default' => 1])
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->content(fn (?Model $record): string => $record ? $record->created_at->format('d/m/Y H:i') : '-'),
                                        Placeholder::make('updated_at')
                                            ->content(fn (?Model $record): string => $record ? $record->updated_at->format('d/m/Y H:i') : '-'),
                                    ])
                            ])->columnSpan(1)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('vendor')
                    ->searchable(),
                TextColumn::make('directory')
                    ->searchable(),
                BooleanColumn::make('is_child')
                    ->label('Is child theme ?')
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DeploymentLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
            'view' => Pages\ViewTheme::route('/{record}/view'),
            'log_view' => Pages\ViewDeploymentLog::route('/{record}/view-log')
        ];
    }
}