<?php

namespace App\Filament\Student\Resources\PaymentResource\Pages;

use App\Filament\Student\Resources\PaymentResource;
use App\Filament\Student\Widgets\CurrentSubscription;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [CurrentSubscription::class];
    }
}
