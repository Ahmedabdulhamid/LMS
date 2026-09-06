<?php

namespace App\Filament\Resources\OrderArchives\Pages;

use App\Filament\Resources\OrderArchives\OrderArchiveResource;
use Filament\Resources\Pages\ListRecords;

class ListOrderArchives extends ListRecords
{
    protected static string $resource = OrderArchiveResource::class;
}
