<?php

namespace App\Observers;

use App\Models\Wishlist;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class WishlistObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Wishlist $wishlist): void
    {
        $this->forgetShowCache($wishlist);
    }

    public function deleted(Wishlist $wishlist): void
    {
        $this->forgetShowCache($wishlist);
    }

    private function forgetShowCache(Wishlist $wishlist): void
    {
        Cache::forget("courses.show.{$wishlist->course_id}");
    }
}
