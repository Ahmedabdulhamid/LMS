<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\PaymentResource\Pages\ListPayments;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('student-panel.navigation.payments');
    }

    public static function getModelLabel(): string
    {
        return __('student-panel.payments.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('student-panel.payments.title');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth('student')->id())
            ->with('items')
            ->latest('id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading(__('student-panel.payments.history'))
            ->columns([
                TextColumn::make('number')->label(__('student-panel.payments.invoice'))->searchable()->copyable(),
                TextColumn::make('items.title')->label(__('student-panel.payments.course'))->listWithLineBreaks()->limitList(2),
                TextColumn::make('total')->label(__('student-panel.payments.amount'))->formatStateUsing(fn ($state, Order $record): string => number_format((float) $state, 2).' '.$record->currency),
                TextColumn::make('paid_at')->label(__('student-panel.payments.date'))->date()->placeholder('—')->sortable(),
                TextColumn::make('payment_status')->label(__('student-panel.payments.status'))->badge()->formatStateUsing(fn (?string $state): string => __('student-panel.payments.'.($state ?: 'pending')))->color(fn (?string $state): string => match ($state) {
                    'paid' => 'success', 'failed' => 'danger', 'refunded' => 'gray', default => 'warning'
                }),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPayments::route('/')];
    }
}
