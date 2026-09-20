<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
#[Fillable('question','answer')]
class Faq extends Model
{
    public const PUBLIC_CACHE_KEY = 'public.faqs';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::PUBLIC_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::PUBLIC_CACHE_KEY));
    }
}
