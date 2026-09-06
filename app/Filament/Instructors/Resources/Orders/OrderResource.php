<?php

namespace App\Filament\Instructors\Resources\Orders;

use App\Filament\Instructors\Resources\Orders\Pages\ListOrders;
use App\Filament\Instructors\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\OrderResource as AdminOrderResource;
use App\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends AdminOrderResource
{
    public static function canAccess(): bool
    {
        return auth('instructor')->check();
    }

    public static function getEloquentQuery(): Builder
    {
        $instructorId = auth('instructor')->id();

        return parent::getEloquentQuery()->whereHas('items', fn (Builder $query): Builder => $query
            ->where('purchasable_type', Course::class)
            ->whereHasMorph('purchasable', [Course::class], fn (Builder $query): Builder => $query
                ->where('instructor_id', $instructorId)));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }
}
