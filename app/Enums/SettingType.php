<?php

namespace App\Enums;

enum SettingType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Text = 'text';
    case Boolean = 'boolean';
    case Json = 'json';
    case File = 'file';

    public function label(): string
    {
        return match ($this) {
            self::String => __('lms.settings.types.string'),
            self::Integer => __('lms.settings.types.integer'),
            self::Text => __('lms.settings.types.text'),
            self::Boolean => __('lms.settings.types.boolean'),
            self::Json => __('lms.settings.types.json'),
            self::File => __('lms.settings.types.file'),
        };
    }

    public static function options(): array
    {
        return array_column(
            array_map(
                fn (self $type) => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ],
                self::cases(),
            ),
            'label',
            'value',
        );
    }
}
