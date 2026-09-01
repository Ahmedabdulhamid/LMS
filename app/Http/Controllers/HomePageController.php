<?php

namespace App\Http\Controllers;

use App\Services\HomePageService;
use Illuminate\Contracts\View\View;

class HomePageController extends Controller
{
    public function __invoke(HomePageService $homePageService): View
    {
        return view('home', [
            'categories' => $homePageService->getLimitCategories(),
            'topRatedCourses' => $homePageService->getLimitTopRatedCourses(),
            'latestCourses' => $homePageService->getLimitLatestCourses(),
            'reviews' => $homePageService->getLimitCourseReviews(),
        ]);
    }

    public function categories(HomePageService $homePageService): View
    {
        return view('catalog', ['type' => 'categories', 'items' => $homePageService->getAllCategories()]);
    }

    public function topRatedCourses(HomePageService $homePageService): View
    {
        return view('catalog', ['type' => 'top-rated', 'items' => $homePageService->getTopRatedCourses()]);
    }

    public function latestCourses(HomePageService $homePageService): View
    {
        return view('catalog', ['type' => 'latest', 'items' => $homePageService->getAllLatestCourses()]);
    }
}
