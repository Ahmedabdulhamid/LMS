<?php

namespace App\Filament\Instructors\Resources\OrderArchives\Pages;

use App\Filament\Instructors\Resources\OrderArchives\OrderArchiveResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderArchives extends ListRecords
{
    protected static string $resource = OrderArchiveResource::class;
}
