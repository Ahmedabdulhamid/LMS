<?php

namespace App\Filament\Resources\OrderArchives;

use App\Filament\Resources\OrderArchives\Pages\ListOrderArchives;
use App\Filament\Resources\OrderArchives\Pages\ViewOrderArchive;
use App\Filament\Support\Orders\OrderArchiveInfolist;
use App\Filament\Support\Orders\OrderArchiveTable;
use App\Models\OrderArchive;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderArchiveResource extends Resource
{
    protected static ?string $model = OrderArchive::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('orders.navigation.archived_orders');
    }

    public static function getModelLabel(): string
    {
        return __('orders.models.archived_order');
    }

    public static function getPluralModelLabel(): string
    {
        return __('orders.navigation.archived_orders');
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

    public static function table(Table $table): Table
    {
        return OrderArchiveTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderArchiveInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderArchives::route('/'),
            'view' => ViewOrderArchive::route('/{record}'),
        ];
    }
}
