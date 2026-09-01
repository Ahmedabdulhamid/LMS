<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;
    protected static bool $canCreateAnother=false;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
