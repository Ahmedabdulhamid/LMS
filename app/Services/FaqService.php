<?php

namespace App\Services;

use App\Models\Faq;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FaqService
{
    private const CACHE_KEY = 'faqs.v3';

    public function retrieveFaqs(): Collection
    {
        $faqs = Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            return Faq::query()
                ->select('id', 'question', 'answer')
                ->latest()
                ->get()
                ->toArray();
        });

        return collect($faqs);
    }

    public function forgetFaqCache(): bool
    {
        Cache::forget('faqs');
        Cache::forget('faqs.v2');

        return Cache::forget(self::CACHE_KEY);
    }
}
