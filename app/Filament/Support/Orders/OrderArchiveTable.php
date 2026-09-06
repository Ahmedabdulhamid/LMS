<?php

namespace App\Filament\Support\Orders;

use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderArchiveTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->select([
                'id', 'original_order_id', 'user_id', 'user_name', 'number', 'total', 'currency', 'status',
                'payment_status', 'archive_reason', 'original_created_at', 'archived_at',
            ]))
            ->columns([
                TextColumn::make('original_order_id')->label(__('orders.fields.original_order_id'))->searchable()->sortable(),
                TextColumn::make('number')->label(__('orders.fields.number'))->searchable()->sortable(),
                TextColumn::make('user_name')->label(__('orders.fields.student'))->searchable(),
                TextColumn::make('total')->label(__('orders.fields.total'))->money(fn ($record): string => $record->currency)->sortable(),
                TextColumn::make('currency')->label(__('orders.fields.currency'))->badge(),
                TextColumn::make('status')->label(__('orders.fields.order_status'))->badge()->formatStateUsing(fn (string $state): string => __('orders.statuses.'.$state)),
                TextColumn::make('payment_status')->label(__('orders.fields.payment_status'))->badge()->formatStateUsing(fn (?string $state): string => __('orders.statuses.'.($state ?: 'unknown'))),
                TextColumn::make('archive_reason')->label(__('orders.fields.archive_reason'))->limit(40)->tooltip(fn ($record): ?string => $record->archive_reason),
                TextColumn::make('original_created_at')->label(__('orders.fields.original_created_at'))->dateTime()->sortable(),
                TextColumn::make('archived_at')->label(__('orders.fields.archived_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('orders.filters.order_status'))->options(OrderTable::orderStatuses()),
                SelectFilter::make('payment_status')->label(__('orders.filters.payment_status'))->options(OrderTable::paymentStatuses()),
                Filter::make('archived_at')->label(__('orders.filters.archive_date'))->schema([
                    DatePicker::make('from')->label(__('orders.filters.from')),
                    DatePicker::make('until')->label(__('orders.filters.until')),
                ])->query(fn (Builder $query, array $data): Builder => $query
                    ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('archived_at', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('archived_at', '<=', $date))),
            ])
            ->recordActions([ViewAction::make()->label(__('orders.actions.view'))])
            ->defaultSort('archived_at', 'desc');
    }
}
