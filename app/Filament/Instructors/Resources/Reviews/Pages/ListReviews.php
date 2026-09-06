<?php

namespace App\Filament\Instructors\Resources\Reviews\Pages;

use App\Filament\Instructors\Resources\Reviews\ReviewResource;
use Filament\Resources\Pages\ListRecords;

class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;
}
