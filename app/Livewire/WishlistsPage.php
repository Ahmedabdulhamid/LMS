<?php

namespace App\Livewire;

use App\Models\Wishlist;
use App\Services\CourseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class WishlistsPage extends Component
{
    use WithPagination;

    public function remove(int $wishlistId, CourseService $courseService): void
    {
        $wishlist = Wishlist::query()
            ->whereKey($wishlistId)
            ->where('user_id', Auth::guard('student')->id())
            ->firstOrFail();

        $courseService->toggleWishlist($wishlist->course, Auth::guard('student')->user());

        $this->dispatch(
            'wishlist-count-updated',
            count: Wishlist::query()->where('user_id', Auth::guard('student')->id())->count(),
        );
    }

    public function thumbnailUrl(?string $path): ?string
    {
        return $path ? Storage::disk(config('lms-upload.disk'))->url($path) : null;
    }

    public function render(): View
    {
        return view('livewire.wishlists-page', [
            'wishlists' => $this->wishlists(),
        ])->layout('layouts.course-public');
    }

    private function wishlists(): LengthAwarePaginator
    {
        return Wishlist::query()
            ->where('user_id', Auth::guard('student')->id())
            ->whereHas('course', fn ($query) => $query->where('is_published', true))
            ->with(['course' => fn ($query) => $query
                ->with(['category', 'instructor'])
                ->withAvg('courseReviews', 'rating')])
            ->latest()
            ->paginate(9);
    }
}
