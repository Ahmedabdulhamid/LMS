<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | Basic information
                |--------------------------------------------------------------------------
                */

                Section::make(__('lms.settings.sections.information'))
                    ->description(
                        __('lms.settings.sections.information_desc')
                    )
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([

                                Select::make('group')
                                    ->label(__('lms.settings.fields.group'))
                                    ->options(SettingGroup::options())
                                    ->default(SettingGroup::General->value)
                                    ->required()
                                    ->native(false)
                                    ->searchable()
                                    ->disabledOn('edit')
                                    ->helperText(
                                        __('lms.settings.help.group')
                                    ),

                                TextInput::make('key')
                                    ->label(__('lms.settings.fields.key'))
                                    ->placeholder(__('lms.settings.placeholders.key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(
                                        ignoreRecord: true,
                                        modifyRuleUsing: function ($rule, Get $get) {
                                            return $rule->where(
                                                'group',
                                                $get('group')
                                            );
                                        }
                                    )
                                    ->regex('/^[a-z0-9._-]+$/')
                                    ->disabledOn('edit')
                                    ->helperText(
                                        __('lms.settings.help.key')
                                    ),

                            ]),

                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([

                                Select::make('type')
                                    ->label(__('lms.settings.fields.type'))
                                    ->options(SettingType::options())
                                    ->default(SettingType::String->value)
                                    ->required()
                                    ->native(false)
                                    ->live()
                                    ->disabledOn('edit')
                                    ->helperText(
                                        __('lms.settings.help.type')
                                    ),

                                Toggle::make('is_public')
                                    ->label(__('lms.settings.fields.public'))
                                    ->default(false)
                                    ->live()
                                    ->disabled(
                                        fn (Get $get): bool => (bool) $get('is_encrypted')
                                    )
                                    ->afterStateHydrated(
                                        function (
                                            Toggle $component,
                                            mixed $state,
                                            Get $get
                                        ): void {
                                            if ($get('is_encrypted')) {
                                                $component->state(false);

                                                return;
                                            }

                                            $component->state(
                                                (bool) $state
                                            );
                                        }
                                    )
                                    ->dehydrateStateUsing(
                                        fn (
                                            mixed $state,
                                            Get $get
                                        ): bool => $get('is_encrypted')
                                                ? false
                                                : (bool) $state
                                    )
                                    ->helperText(
                                        __('lms.settings.help.public')
                                    ),

                                Toggle::make('is_encrypted')
                                    ->label(__('lms.settings.fields.encrypted'))
                                    ->default(false)
                                    ->live()
                                    ->disabledOn('edit')
                                    ->afterStateUpdated(
                                        function (
                                            bool $state,
                                            Set $set
                                        ): void {
                                            if ($state) {
                                                $set(
                                                    'is_public',
                                                    false
                                                );
                                            }
                                        }
                                    )
                                    ->helperText(
                                        __('lms.settings.help.encrypted')
                                    ),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | Dynamic value
                |--------------------------------------------------------------------------
                */

                Section::make(__('lms.settings.sections.value'))
                    ->description(
                        __('lms.settings.sections.value_desc')
                    )
                    ->icon('heroicon-o-pencil-square')
                    ->schema(
                        fn (Get $get): array => self::getValueSchema($get)
                    ),

            ]);
    }

    private static function getValueSchema(Get $get): array
    {
        /*
        |--------------------------------------------------------------------------
        | Encrypted value
        |--------------------------------------------------------------------------
        |
        | An encrypted value takes priority over the normal setting type.
        |
        | Example:
        |
        | smtp_password
        | stripe_secret
        | api_secret
        |
        */

        if ((bool) $get('is_encrypted')) {
            return [
                TextInput::make('value')
                    ->label(__('lms.settings.fields.secret_value'))
                    ->password()
                    ->revealable()
                    ->autocomplete(false)
                    ->maxLength(65535)
                    ->required(
                        fn (string $operation): bool => $operation === 'create'
                    )
                    ->helperText(
                        __('lms.settings.help.secret')
                    ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Normal values
        |--------------------------------------------------------------------------
        */

        return match ($get('type')) {

            /*
            |--------------------------------------------------------------------------
            | Boolean
            |--------------------------------------------------------------------------
            */

            SettingType::Boolean->value => [
                Toggle::make('value')
                    ->label(__('lms.settings.fields.value'))
                    ->default(false)
                    ->afterStateHydrated(
                        function (
                            Toggle $component,
                            mixed $state
                        ): void {
                            $component->state(
                                filter_var(
                                    $state,
                                    FILTER_VALIDATE_BOOLEAN
                                )
                            );
                        }
                    )
                    ->dehydrateStateUsing(
                        fn (mixed $state): string => $state ? '1' : '0'
                    )
                    ->helperText(
                        __('lms.settings.help.boolean')
                    ),
            ],

            SettingType::Integer->value => [
                TextInput::make('value')->label(__('lms.settings.fields.number'))->numeric()->step(1)->columnSpanFull(),
            ],

            SettingType::Text->value => [
                Textarea::make('value')->label(__('lms.settings.fields.value'))->rows(7)->autosize()->maxLength(65535)->columnSpanFull(),
            ],

            /*
            |--------------------------------------------------------------------------
            | JSON
            |--------------------------------------------------------------------------
            */

            SettingType::Json->value => [
                KeyValue::make('value')
                    ->label(__('lms.settings.fields.json_value'))
                    ->keyLabel(__('lms.settings.fields.json_key'))
                    ->valueLabel(__('lms.settings.fields.json_item_value'))
                    ->addActionLabel(__('lms.settings.actions.add_item'))
                    ->reorderable()
                    ->afterStateHydrated(
                        function (
                            KeyValue $component,
                            mixed $state
                        ): void {
                            if (blank($state)) {
                                $component->state([]);

                                return;
                            }

                            if (is_array($state)) {
                                $component->state($state);

                                return;
                            }

                            $decoded = json_decode(
                                $state,
                                true
                            );

                            $component->state(
                                is_array($decoded)
                                    ? $decoded
                                    : []
                            );
                        }
                    )
                    ->dehydrateStateUsing(
                        fn (?array $state): string => json_encode(
                            $state ?? [],
                            JSON_UNESCAPED_UNICODE
                            | JSON_UNESCAPED_SLASHES
                            | JSON_THROW_ON_ERROR
                        )
                    )
                    ->columnSpanFull(),
            ],

            /*
            |--------------------------------------------------------------------------
            | File
            |--------------------------------------------------------------------------
            */

            SettingType::File->value => [
                FileUpload::make('value')
                    ->label(__('lms.settings.fields.file'))
                    ->disk(config('lms-upload.disk'))
                    ->directory('settings')
                    ->visibility('public')
                    ->acceptedFileTypes(fn (Get $get): array => in_array($get('key'), ['website_logo', 'website_icon', 'favicon'], true)
                        ? ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/x-icon', 'image/vnd.microsoft.icon']
                        : [])
                    ->downloadable()
                    ->openable()
                    ->maxSize(5120)
                    ->helperText(
                        __('lms.settings.help.file')
                    )
                    ->columnSpanFull(),
            ],

            /*
            |--------------------------------------------------------------------------
            | String
            |--------------------------------------------------------------------------
            */

            default => [
                TextInput::make('value')
                    ->label(__('lms.settings.fields.value'))
                    ->placeholder(__('lms.settings.placeholders.value'))
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ],

        };
    }
}
