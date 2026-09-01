<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use App\Services\FaqService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

class ListFaqs extends Page
{
    protected static string $resource = FaqResource::class;

    protected string $view = 'filament.resources.faqs.pages.list-faqs';

    public function getFaqs(): Collection
    {
        return app(FaqService::class)->retrieveFaqs();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
