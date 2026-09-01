<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlansRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptionPlans';

    protected static ?string $title = 'Subscription Plans';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lms.navigation.subscription_plans');
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('price')->money(fn ($record): string => $record->currency)->sortable(),
                TextColumn::make('duration_value')->label(__('lms.common.duration'))->formatStateUsing(
                    fn ($state, $record): string => $state.' '.$record->duration_unit->value,
                )->sortable(),
                IconColumn::make('is_active')->label(__('lms.common.active'))->boolean()->sortable(),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect()->recordSelectSearchColumns(['name']),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DetachBulkAction::make()]),
            ]);
    }
}
