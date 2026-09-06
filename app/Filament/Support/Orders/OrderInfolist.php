<?php

namespace App\Filament\Support\Orders;

use App\Enums\OrderStatus;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('orders.sections.information'))->columns(3)->schema([
                TextEntry::make('number')->label(__('orders.fields.number'))->copyable(),
                TextEntry::make('user.name')->label(__('orders.fields.student')),
                TextEntry::make('user.email')->label(__('orders.fields.email')),
                TextEntry::make('total')->label(__('orders.fields.total'))->money(fn ($record): string => $record->currency),
                TextEntry::make('payment_status')->label(__('orders.fields.payment_status'))->badge()->formatStateUsing(fn (?string $state): string => __('orders.statuses.'.($state ?: 'unknown'))),
                TextEntry::make('status')->label(__('orders.fields.order_status'))->badge()->formatStateUsing(fn (OrderStatus|string $state): string => __('orders.statuses.'.($state instanceof OrderStatus ? $state->value : $state))),
                TextEntry::make('created_at')->label(__('orders.fields.created_at'))->dateTime(),
                TextEntry::make('paid_at')->label(__('orders.fields.paid_at'))->dateTime()->placeholder('—'),
            ]),
            Section::make(__('orders.sections.items'))->schema([
                RepeatableEntry::make('items')->hiddenLabel()->columns(3)->schema([
                    TextEntry::make('title')->label(__('orders.fields.course')),
                    TextEntry::make('unit_price')->label(__('orders.fields.price'))->money('EGP'),
                    TextEntry::make('total')->label(__('orders.fields.total'))->money('EGP'),
                ]),
            ]),
        ]);
    }
}
