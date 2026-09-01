<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    /**
     * Create a new class instance.
     */

    public function __construct()
    {
        //
    }
    public function retrievCategories()
    {
        return Cache::remember('categories', now()->addMinutes(10), function () {
            return Category::query()->pluck('name','id')->toArray();
        });
    }
    public function forgetCategoryCache(){
        return Cache::forget('categories');
    }
}
