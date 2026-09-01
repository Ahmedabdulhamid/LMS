<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Services\SettingService;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshCache')
                ->label(__('lms.settings.actions.refresh_cache'))->icon('heroicon-o-arrow-path')->color('gray')
                ->action(function (): void {
                    app(SettingService::class)->flushCache();
                    Notification::make()->title(__('lms.settings.messages.cache_refreshed'))->success()->send();
                }),
            CreateAction::make()->label(__('lms.settings.actions.new'))->icon('heroicon-o-plus'),
        ];
    }

    public function getTitle(): string
    {
        return __('lms.settings.title');
    }

    public function getSubheading(): ?string
    {
        return __('lms.settings.subtitle');
    }
}
