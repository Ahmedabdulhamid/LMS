<?php

namespace App\Filament\Support\Orders;

use App\Enums\OrderStatus;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label(__('orders.fields.number'))->searchable()->sortable(),
                TextColumn::make('user.name')->label(__('orders.fields.student'))->searchable(['name', 'email'])->sortable(),
                TextColumn::make('items.title')->label(__('orders.fields.course'))->listWithLineBreaks()->limitList(2),
                TextColumn::make('total')->label(__('orders.fields.total'))->money(fn ($record): string => $record->currency)->sortable(),
                TextColumn::make('currency')->label(__('orders.fields.currency'))->badge(),
                TextColumn::make('payment_status')->label(__('orders.fields.payment_status'))->badge()->formatStateUsing(fn (?string $state): string => __('orders.statuses.'.($state ?: 'unknown'))),
                TextColumn::make('status')->label(__('orders.fields.order_status'))->badge()->formatStateUsing(fn (OrderStatus|string $state): string => __('orders.statuses.'.($state instanceof OrderStatus ? $state->value : $state))),
                TextColumn::make('created_at')->label(__('orders.fields.created_at'))->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label(__('orders.filters.order_status'))->options(self::orderStatuses()),
                SelectFilter::make('payment_status')->label(__('orders.filters.payment_status'))->options(self::paymentStatuses()),
                SelectFilter::make('user_id')->label(__('orders.filters.user'))->relationship('user', 'name')->searchable()->preload(),
                Filter::make('created_at')->label(__('orders.filters.date_range'))->schema([
                    DatePicker::make('from')->label(__('orders.filters.from')),
                    DatePicker::make('until')->label(__('orders.filters.until')),
                ])->query(fn (Builder $query, array $data): Builder => $query
                    ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                    ->when($data['until'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))),
            ])
            ->recordActions([ViewAction::make()->label(__('orders.actions.view'))])
            ->defaultSort('created_at', 'desc');
    }

    public static function orderStatuses(): array
    {
        return collect(OrderStatus::cases())->mapWithKeys(fn (OrderStatus $status): array => [
            $status->value => __('orders.statuses.'.$status->value),
        ])->all();
    }

    public static function paymentStatuses(): array
    {
        return collect(['pending', 'completed', 'failed', 'refunded'])->mapWithKeys(fn (string $status): array => [
            $status => __('orders.statuses.'.$status),
        ])->all();
    }
}
