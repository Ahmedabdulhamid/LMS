<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class FaqPageController extends Controller
{
    public function __invoke(): View
    {
        $faqs = Cache::remember(Faq::PUBLIC_CACHE_KEY, now()->addHours(12), fn () => Faq::query()
            ->latest('id')
            ->get(['id', 'question', 'answer']));

        return view('faq', compact('faqs'));
    }
}
