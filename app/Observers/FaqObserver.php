<?php

namespace App\Observers;

use App\Models\Faq;
use App\Services\FaqService;

class FaqObserver
{
    /**
     * Handle the Faq "created" event.
     */
    public function created(Faq $faq): void
    {
        app(FaqService::class)->forgetFaqCache();
        app(FaqService::class)->retrieveFaqs();

    }

    /**
     * Handle the Faq "updated" event.
     */
    public function updated(Faq $faq): void
    {
        app(FaqService::class)->forgetFaqCache();
        app(FaqService::class)->retrieveFaqs();
    }

    /**
     * Handle the Faq "deleted" event.
     */
    public function deleted(Faq $faq): void
    {
         app(FaqService::class)->forgetFaqCache();
         app(FaqService::class)->retrieveFaqs();
    }

    /**
     * Handle the Faq "restored" event.
     */
    public function restored(Faq $faq): void
    {
        //
    }

    /**
     * Handle the Faq "force deleted" event.
     */
    public function forceDeleted(Faq $faq): void
    {
        //
    }
}
