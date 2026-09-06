<?php

namespace App\Filament\Support\Orders;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderArchiveInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('orders.sections.information'))->columns(3)->schema([
                TextEntry::make('original_order_id')->label(__('orders.fields.original_order_id')),
                TextEntry::make('number')->label(__('orders.fields.number'))->copyable(),
                TextEntry::make('user_name')->label(__('orders.fields.student'))->placeholder('—'),
                TextEntry::make('user_email')->label(__('orders.fields.email'))->placeholder('—'),
                TextEntry::make('total')->label(__('orders.fields.total'))->money(fn ($record): string => $record->currency),
                TextEntry::make('currency')->label(__('orders.fields.currency')),
                TextEntry::make('status')->label(__('orders.fields.order_status'))->badge()->formatStateUsing(fn (string $state): string => __('orders.statuses.'.$state)),
                TextEntry::make('payment_status')->label(__('orders.fields.payment_status'))->badge()->formatStateUsing(fn (?string $state): string => __('orders.statuses.'.($state ?: 'unknown'))),
                TextEntry::make('archive_reason')->label(__('orders.fields.archive_reason')),
                TextEntry::make('paid_at')->label(__('orders.fields.paid_at'))->dateTime()->placeholder('—'),
                TextEntry::make('original_created_at')->label(__('orders.fields.original_created_at'))->dateTime(),
                TextEntry::make('archived_at')->label(__('orders.fields.archived_at'))->dateTime(),
            ]),
            Section::make(__('orders.sections.items'))->schema([
                RepeatableEntry::make('items')->hiddenLabel()->columns(3)->schema([
                    TextEntry::make('title')->label(__('orders.fields.course')),
                    TextEntry::make('unit_price')->label(__('orders.fields.price'))->money('EGP'),
                    TextEntry::make('metadata.quantity')->label(__('orders.fields.quantity'))->default(1),
                ]),
            ])->collapsible(),
            Section::make(__('orders.sections.payment_transactions'))->schema([
                RepeatableEntry::make('payment_transactions')->hiddenLabel()->columns(5)->schema([
                    TextEntry::make('provider')->label(__('orders.fields.provider')),
                    TextEntry::make('provider_transaction_id')->label(__('orders.fields.transaction_id'))->copyable(),
                    TextEntry::make('amount_cents')->label(__('orders.fields.amount'))->formatStateUsing(fn ($state): string => number_format(((int) $state) / 100, 2)),
                    TextEntry::make('status')->label(__('orders.fields.status'))->badge(),
                    TextEntry::make('created_at')->label(__('orders.fields.created_at'))->dateTime(),
                ]),
            ])->collapsible(),
            Section::make(__('orders.sections.webhook_events'))->schema([
                RepeatableEntry::make('payment_webhook_events')->hiddenLabel()->columns(3)->schema([
                    TextEntry::make('event_type')->label(__('orders.fields.event_type')),
                    TextEntry::make('status')->label(__('orders.fields.status'))->badge(),
                    TextEntry::make('received_at')->label(__('orders.fields.received_at'))->dateTime(),
                ]),
            ])->collapsible(),
        ]);
    }
}
