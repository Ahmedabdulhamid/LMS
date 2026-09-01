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
        static::saved(fn () => app(SettingService::class)->flushCache());
        static::deleted(fn () => app(SettingService::class)->flushCache());
    }

    public function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
        ];
    }

}
