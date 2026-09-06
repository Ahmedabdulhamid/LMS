<?php

namespace App\Filament\Instructors\Resources\Payments\Pages;

use App\Filament\Instructors\Resources\Payments\PaymentResource;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;
}
