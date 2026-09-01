<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('lms.settings.table.setting'))
                    ->description(fn ($record): string => "{$record->group} · {$record->type}")
                    ->icon('heroicon-o-command-line')
                    ->weight('semibold')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('lms.settings.messages.key_copied')),
                TextColumn::make('value')
                    ->label(__('lms.settings.table.current_value'))
                    ->formatStateUsing(fn ($state, $record): string => $record->is_encrypted
                        ? '••••••••••••'
                        : match ($record->type) {
                            'boolean' => filter_var($state, FILTER_VALIDATE_BOOLEAN) ? __('lms.settings.table.enabled') : __('lms.settings.table.disabled'),
                            'json' => __('lms.settings.table.structured_data'),
                            'file' => filled($state) ? __('lms.settings.table.uploaded_file') : __('lms.settings.table.no_file'),
                            default => filled($state) ? (string) $state : __('lms.settings.table.not_configured'),
                        })
                    ->limit(44)
                    ->color(fn ($record): string => $record->is_encrypted ? 'warning' : 'gray')
                    ->icon(fn ($record): ?string => $record->is_encrypted ? 'heroicon-o-lock-closed' : null),
                TextColumn::make('group')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => SettingGroup::tryFrom($state)?->label() ?? ucfirst($state))
                    ->color(fn (string $state): string => SettingGroup::tryFrom($state)?->color() ?? 'gray')
                    ->sortable(),
                IconColumn::make('is_public')->label(__('lms.settings.table.public'))->boolean(),
                IconColumn::make('is_encrypted')
                    ->label(__('lms.settings.table.protected'))->boolean()
                    ->trueIcon('heroicon-o-shield-check')->falseIcon('heroicon-o-minus')
                    ->trueColor('success')->falseColor('gray'),
                TextColumn::make('updated_at')->label(__('lms.settings.table.last_update'))->since()->dateTimeTooltip()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('group')->options(SettingGroup::options())->multiple(),
                SelectFilter::make('type')->options(SettingType::options())->multiple(),
                TernaryFilter::make('is_public')->label(__('lms.settings.filters.public')),
                TernaryFilter::make('is_encrypted')->label(__('lms.settings.filters.protected')),
            ])
            ->defaultSort('group')
            ->striped()
            ->emptyStateIcon('heroicon-o-adjustments-horizontal')
            ->emptyStateHeading(__('lms.settings.messages.empty_title'))
            ->emptyStateDescription(__('lms.settings.messages.empty_description'))
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
