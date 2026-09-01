<?php

namespace App\Observers;

use App\Models\User;
use App\Services\HomePageService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(User $user): void
    {
        app(HomePageService::class)->forgetCache();
    }

    public function deleted(User $user): void
    {
        app(HomePageService::class)->forgetCache();
    }

    public function restored(User $user): void
    {
        app(HomePageService::class)->forgetCache();
    }
}
