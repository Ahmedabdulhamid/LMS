<?php

namespace App\Filament\Resources\SubscriptionPlans\Schemas;

use App\Enums\SubscriptionDurationUnit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('lms.subscription_plans.fields.name'))
                ->required()->maxLength(255),
            Textarea::make('description')
                ->label(__('lms.subscription_plans.fields.description'))
                ->columnSpanFull(),
            TextInput::make('price')
                ->label(__('lms.subscription_plans.fields.price'))
                ->required()->numeric()->minValue(0),
            TextInput::make('currency')
                ->label(__('lms.subscription_plans.fields.currency'))
                ->required()
                ->default('EGP')
                ->length(3)
                ->dehydrateStateUsing(fn (string $state): string => strtoupper($state)),
            TextInput::make('duration_value')
                ->label(__('lms.subscription_plans.fields.duration_value'))
                ->required()->integer()->minValue(1),
            Select::make('duration_unit')
                ->label(__('lms.subscription_plans.fields.duration_unit'))
                ->required()
                ->options(collect(SubscriptionDurationUnit::cases())
                    ->mapWithKeys(fn (SubscriptionDurationUnit $unit): array => [$unit->value => ucfirst($unit->value)])
                    ->all()),
            Toggle::make('is_active')
                ->label(__('lms.subscription_plans.fields.is_active'))
                ->required()->default(true),
        ]);
    }
}
