<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Tabs::make(__('lms.faqs.tab_title'))
               ->tabs([
                Tab::make(__('lms.faqs.question'))->schema([
                    TextInput::make('question')
                        ->label(__('lms.faqs.fields.question')),
                ]),
                 Tab::make(__('lms.faqs.answer'))->schema([
                    TextInput::make('answer')
                        ->label(__('lms.faqs.fields.answer')),
                ])

               ])->columnSpanFull()
            ]);
    }
}
