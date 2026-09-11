<?php

use App\Http\Controllers\CourseVideoUploadController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VideoStreamController;
use App\Http\Controllers\UserDeviceTokenController;
use App\Livewire\CheckoutOrder;
use App\Livewire\ContactPage;
use App\Livewire\LearnCourse;
use App\Livewire\MyCourses;
use App\Livewire\ShowCourse;
use App\Livewire\SubscriptionPlans;
use App\Livewire\WishlistsPage;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:instructor', 'throttle:300,1'])
    ->prefix('api/instructor/courses/{course}/video-uploads')
    ->name('instructor.course-video-uploads.')
    ->controller(CourseVideoUploadController::class)
    ->group(function (): void {
        Route::post('/initiate', 'initiate')->name('initiate');
        Route::post('/initiate-attachment', 'initiateAttachment')->name('initiate-attachment');
        Route::post('/sign-part', 'signPart')->name('sign-part');
        Route::post('/complete', 'complete')->name('complete');
        Route::delete('/abort', 'abort')->name('abort');
    });

Route::middleware('throttle:120,1')
    ->prefix('courses/{course}/videos/{video}/stream')
    ->name('course-videos.stream.')
    ->controller(VideoStreamController::class)
    ->group(function (): void {
        Route::get('/master.m3u8', 'master')->name('master');
        Route::get('/{quality}/index.m3u8', 'variant')->name('variant');
    });

Route::middleware(['auth:student', 'throttle:60,1'])
    ->prefix('api/student/device-tokens')
    ->name('student.device-tokens.')
    ->controller(UserDeviceTokenController::class)
    ->group(function (): void {
        Route::post('/', 'store')->name('store');
        Route::delete('/', 'destroy')->name('destroy');
    });

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], strict: true), 404);

    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

Route::get('/', HomePageController::class)->name('home');
Route::get('/categories', [HomePageController::class, 'categories'])->name('categories.index');
Route::get('/courses/top-rated', [HomePageController::class, 'topRatedCourses'])->name('courses.top-rated');
Route::get('/courses/latest', [HomePageController::class, 'latestCourses'])->name('courses.latest');
Route::get('/wishlists', WishlistsPage::class)->middleware('auth:student')->name('wishlists.index');
Route::get('/my-courses', MyCourses::class)->middleware('auth:student')->name('my-courses.index');
Route::get('/subscription-plans', SubscriptionPlans::class)->name('subscription-plans.index');
Route::get('/contact', ContactPage::class)->name('contact.index');
Route::get('/checkout/orders/{order:number}', CheckoutOrder::class)
    ->middleware('auth:student')
    ->name('checkout.orders.show');
Route::get('/courses/{course:slug}/learn', LearnCourse::class)
    ->middleware('auth:student')
    ->name('courses.learn');
Route::get('/courses/{course:slug}', ShowCourse::class)->name('courses.show');

// Payment callback - Redirect from Paymob
// User is redirected here after payment attempt (success or failure)
// This endpoint only shows status - payment is confirmed via webhook
Route::get('/payment/callback', [PaymentController::class, 'callback'])
    ->middleware('auth:student')
    ->name('payment.callback');
