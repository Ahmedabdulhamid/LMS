<?php

namespace App\Filament\Resources\Settings\Pages;

use App\Filament\Resources\Settings\SettingResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\SettingService;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data['is_encrypted'] ?? false) {
            $data['is_public'] = false;
            $data['value'] = app(SettingService::class)->encryptValue($data['value'] ?? null, true);
        }

        return $data;
    }
}
