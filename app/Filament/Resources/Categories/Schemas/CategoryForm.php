<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('lms.category.fields.name'))
                    ->required(),

                Textarea::make('small_description')
                    ->label(__('lms.category.fields.small_description'))
                    ->required(),
                FileUpload::make('icon')
                    ->label(__('lms.category.fields.icon'))
                    ->directory('categories/icons')
                    ->disk(config('lms-upload.disk'))
                    ->visibility('private')
                    ->image()
                    ->imageEditor()
                    ->maxSize(2048)
                    ->required(),
            ]);
    }
}
