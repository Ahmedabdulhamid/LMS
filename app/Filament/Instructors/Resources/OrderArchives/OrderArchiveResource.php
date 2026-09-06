<?php

namespace App\Filament\Instructors\Resources\OrderArchives;

use App\Filament\Instructors\Resources\OrderArchives\Pages\ListOrderArchives;
use App\Filament\Instructors\Resources\OrderArchives\Pages\ViewOrderArchive;
use App\Filament\Resources\OrderArchives\OrderArchiveResource as AdminOrderArchiveResource;
use Illuminate\Database\Eloquent\Builder;

class OrderArchiveResource extends AdminOrderArchiveResource
{
    public static function canAccess(): bool
    {
        return auth('instructor')->check();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereJsonContains('instructor_ids', auth('instructor')->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderArchives::route('/'),
            'view' => ViewOrderArchive::route('/{record}'),
        ];
    }
}
