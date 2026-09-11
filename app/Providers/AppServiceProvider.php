<?php

namespace App\Providers;

use App\Events\ContactCreated;
use App\Events\OrderCreated;
use App\Events\SubscriptionCreated;
use App\Listeners\SendContactNotification;
use App\Listeners\SendOrderNotification;
use App\Listeners\SendSubscriptionNotification;
use App\Models\Category;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\CourseRequirement;
use App\Models\CourseReview;
use App\Models\CourseVideo;
use App\Models\Enrollment;
use App\Models\Faq;
use App\Models\Instructor;
use App\Models\IntendedLearner;
use App\Models\Section;
use App\Models\User;
use App\Models\UserCourseProgress;
use App\Models\VideoAttachment;
use App\Models\Wishlist;
use App\Observers\CategoryObserver;
use App\Observers\CourseObserver;
use App\Observers\CoursePurchaseObserver;
use App\Observers\CourseRequirementObserver;
use App\Observers\CourseVideoObserver;
use App\Observers\EnrollmentObserver;
use App\Observers\FaqObserver;
use App\Observers\InstructorObserver;
use App\Observers\IntendedLearnerObserver;
use App\Observers\PaymentObserver;
use App\Observers\ReviewObserver;
use App\Observers\SectionObserver;
use App\Observers\UserCourseProgressObserver;
use App\Observers\UserObserver;
use App\Observers\VideoAttachmentObserver;
use App\Observers\WishlistObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            request()->isSecure()
            || str_contains((string) request()->header('X-Forwarded-Proto', ''), 'https')
            || str_starts_with((string) config('app.url'), 'https://')
        ) {
            URL::forceScheme('https');
            URL::forceRootUrl(rtrim((string) config('app.url'), '/'));
        }

        Instructor::observe(InstructorObserver::class);
        Category::observe(CategoryObserver::class);
        Course::observe(CourseObserver::class);
        CoursePurchase::observe([CoursePurchaseObserver::class, PaymentObserver::class]);
        CourseReview::observe(ReviewObserver::class);
        IntendedLearner::observe(IntendedLearnerObserver::class);
        CourseRequirement::observe(CourseRequirementObserver::class);
        CourseVideo::observe(CourseVideoObserver::class);
        Faq::observe(FaqObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        Section::observe(SectionObserver::class);
        User::observe(UserObserver::class);
        UserCourseProgress::observe(UserCourseProgressObserver::class);
        VideoAttachment::observe(VideoAttachmentObserver::class);
        Wishlist::observe(WishlistObserver::class);
        Event::listen(
            ContactCreated::class,
            SendContactNotification::class,
        );
        Event::listen(OrderCreated::class, SendOrderNotification::class);
        Event::listen(SubscriptionCreated::class, SendSubscriptionNotification::class);
    }
}
