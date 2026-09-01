<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\CategoryService;
use App\Services\HomePageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        app(HomePageService::class)->forgetCache();
        app(CategoryService::class)->forgetCategoryCache();
        app(CategoryService::class)->retrievCategories();
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        if ($category->isDirty('icon')) {
            // Delete the old icon file if it exists
            $oldIconPath = $category->getOriginal('icon');
            if ($oldIconPath && Storage::disk(config('lms-upload.disk'))->exists($oldIconPath)) {
                Storage::disk(config('lms-upload.disk'))->delete($oldIconPath);
            }
        }

          app(HomePageService::class)->forgetCache();
          app(CategoryService::class)->forgetCategoryCache();
          app(CategoryService::class)->retrievCategories();

    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        if ($category->icon && Storage::disk(config('lms-upload.disk'))->exists($category->icon)) {
            Storage::disk(config('lms-upload.disk'))->delete($category->icon);
        }
        app(HomePageService::class)->forgetCache();
        app(CategoryService::class)->forgetCategoryCache();
        app(CategoryService::class)->retrievCategories();
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        //
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        //
    }
}
