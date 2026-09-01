<?php

namespace App\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('lms.admin.fields.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('lms.fields.email'))
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('password')
                    ->label(__('lms.admin.fields.password'))
                    ->password()
                    ->required(),
                TextInput::make('phone')
                    ->label(__('lms.admin.fields.phone'))
                    ->tel()

                    ->default(null),
                FileUpload::make('image')
                    ->label(__('lms.admin.fields.image'))
                    ->disk(config('lms-upload.disk'))
                    ->directory('admins/profile_pictures')
                    ->image(),
            ]);
    }
}
