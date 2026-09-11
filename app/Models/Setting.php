<?php

namespace App\Models;

use App\Services\SettingService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'value', 'type', 'is_public', 'is_encrypted'])]
class Setting extends Model
{
    protected static function booted(): void
    {
        $invalidate = function (Setting $setting): void {
            app(SettingService::class)->forget();
            if ($setting->getConnection()->transactionLevel() > 0) {
                $setting->getConnection()->afterCommit(fn () => app(SettingService::class)->forget());
            }
        };
        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
        ];
    }
}
