<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('lms.coupons.fields.code'))
                    ->required(),
                Select::make('discount_type')
                    ->label(__('lms.coupons.fields.discount_type'))
                    ->options([
                        'percentage' => __('lms.coupons.discount_types.percentage'),
                        'fixed' => __('lms.coupons.discount_types.fixed'),
                    ])
                    ->default('percentage')
                    ->live()
                    ->required(),
                TextInput::make('discount_value')
                    ->label(__('lms.coupons.fields.discount_value'))
                    ->required()
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(fn (Get $get): ?int => $get('discount_type') === 'percentage' ? 100 : null),
                TextInput::make('max_uses')
                    ->label(__('lms.coupons.fields.max_uses'))
                    ->required()
                    ->integer()
                    ->numeric()
                    ->minValue(1)
                    ->default(1),
                DateTimePicker::make('start_date')
                    ->label(__('lms.coupons.fields.start_date'))
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->minDate(now())
                    ->before('end_date')
                    ->maxDate(fn (Get $get) => $get('end_date')),

                DateTimePicker::make('end_date')
                    ->label(__('lms.coupons.fields.end_date'))
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->after('start_date')
                    ->minDate(fn (Get $get) => $get('start_date')),
                Toggle::make('is_active')
                    ->label(__('lms.coupons.fields.is_active'))
                    ->default(true)
                    ->required(),
            ]);
    }
}
