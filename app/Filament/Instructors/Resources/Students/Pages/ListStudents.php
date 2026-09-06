<?php

namespace App\Filament\Instructors\Resources\Students\Pages;

use App\Filament\Instructors\Resources\Students\StudentResource;
use Filament\Resources\Pages\ListRecords;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;
}
