<?php

namespace Northlab\FilamentThemeManager\Filament\Resources\ThemeResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\BadgeColumn;
use Northlab\FilamentThemeManager\Enum\DeploymentTypeEnum;
use Northlab\FilamentThemeManager\Enum\DeploymentStatusEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Northlab\FilamentThemeManager\Jobs\RetryingCloneJob;
use Northlab\FilamentThemeManager\Jobs\RetryingDeployJob;

class DeploymentLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'deployment_logs';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $title = "Deployment Logs";

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->formatStateUsing(fn ($state) => ucwords($state))
                    ->colors([
                        'success' => fn ($state) => $state === DeploymentStatusEnum::SUCCESSED()->value,
                        'warning' => fn ($state) => $state === DeploymentStatusEnum::PROCESSING()->value,
                        'danger' => fn ($state) => $state === DeploymentStatusEnum::FAILED()->value,
                    ]),

                TextColumn::make('created_at')
                    ->label('Process Started')
                    ->dateTime('d/m/Y H:i:s'),

                TextColumn::make('process_end_at')
                    ->label('Process End')
                    ->dateTime('d/m/Y H:i:s'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('viewLog')
                    ->url(fn (Model $record) => route('filament.resources.themes.log_view', [
                        'record' => $record->theme,
                        'log_id' => $record->id
                    ]))
                    ->icon('heroicon-o-eye'),

                Action::make('retry')
                    ->icon('heroicon-o-refresh')
                    ->action(fn (Model $record) => match($record->meta['type'] ?? null) {
                        DeploymentTypeEnum::CLONE()->value => RetryingCloneJob::dispatch($record),
                        DeploymentTypeEnum::DEPLOY()->value => RetryingDeployJob::dispatch($record),
                        default => null
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Model $record) => $record->status === DeploymentStatusEnum::FAILED()->value)
                    ->color('danger'),
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}