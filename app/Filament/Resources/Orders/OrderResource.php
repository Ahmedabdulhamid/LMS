<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Support\Orders\OrderInfolist;
use App\Filament\Support\Orders\OrderTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('orders.navigation.orders');
    }

    public static function getModelLabel(): string
    {
        return __('orders.models.order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('orders.navigation.orders');
    }

    public static function canAccess(): bool
    {
        return auth('admin')->check();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'user:id,name,email',
            'items:id,order_id,title,unit_price,discount_amount,total,metadata',
        ]);
    }

    public static function table(Table $table): Table
    {
        return OrderTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
