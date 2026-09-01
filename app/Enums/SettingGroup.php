<?php

namespace App\Enums;

enum SettingGroup: string
{
    case General = 'general';
    case Appearance = 'appearance';
    case Localization = 'localization';
    case Notifications = 'notifications';
    case Payment = 'payment';
    case Smtp = 'smtp';
    case Integrations = 'integrations';
    case Security = 'security';

    public function label(): string
    {
        return match ($this) {
            self::General => __('lms.settings.groups.general'),
            self::Appearance => __('lms.settings.groups.appearance'),
            self::Localization => __('lms.settings.groups.localization'),
            self::Notifications => __('lms.settings.groups.notifications'),
            self::Payment => __('lms.settings.groups.payment'),
            self::Smtp => __('lms.settings.groups.smtp'),
            self::Integrations => __('lms.settings.groups.integrations'),
            self::Security => __('lms.settings.groups.security'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::General => 'primary',
            self::Appearance, self::Integrations => 'info',
            self::Localization, self::Payment => 'success',
            self::Notifications, self::Smtp => 'warning',
            self::Security => 'danger',
        };
    }

    public static function options(): array
    {
        return array_column(
            array_map(
                fn (self $group) => [
                    'value' => $group->value,
                    'label' => $group->label(),
                ],
                self::cases(),
            ),
            'label',
            'value',
        );
    }
}
