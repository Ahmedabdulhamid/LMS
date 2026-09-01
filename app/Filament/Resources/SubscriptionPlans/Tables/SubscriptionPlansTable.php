<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('price')->money(fn ($record): string => $record->currency)->sortable(),
                TextColumn::make('duration_value')->label(__('lms.common.duration'))->formatStateUsing(
                    fn ($state, $record): string => $state.' '.$record->duration_unit->value,
                ),
                TextColumn::make('courses_count')->counts('courses')->label(__('lms.common.courses')),
                TextColumn::make('subscriptions_count')->counts('subscriptions')->label(__('lms.common.subscriptions')),
                IconColumn::make('is_active')->label(__('lms.common.active'))->boolean(),
                TextColumn::make('created_at')->label(__('lms.common.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (Model $record) => $record->subscriptions()->exists()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
