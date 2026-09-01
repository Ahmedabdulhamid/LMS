<?php

namespace App\Observers;

use App\Models\Instructor;
use App\Services\HomePageService;
use Illuminate\Support\Facades\Storage;

class InstructorObserver
{
    /**
     * Handle the Instructor "created" event.
     */
    public function created(Instructor $instructor): void
    {
        //
    }

    /**
     * Handle the Instructor "updated" event.
     */
    public function updated(Instructor $instructor): void
    {
        if ($instructor->isDirty('profile_picture')) {
            $originalProfilePicture = $instructor->getOriginal('profile_picture');

            if ($originalProfilePicture && Storage::disk(config('lms-upload.disk'))->exists($originalProfilePicture)) {
                Storage::disk(config('lms-upload.disk'))->delete($originalProfilePicture);
            }
        }

        app(HomePageService::class)->forgetCache();

    }

    /**
     * Handle the Instructor "deleted" event.
     */
    public function deleted(Instructor $instructor): void
    {
        if ($instructor->profile_picture && Storage::disk(config('lms-upload.disk'))->exists($instructor->profile_picture)) {
            Storage::disk(config('lms-upload.disk'))->delete($instructor->profile_picture);

        }

        app(HomePageService::class)->forgetCache();
    }

    /**
     * Handle the Instructor "restored" event.
     */
    public function restored(Instructor $instructor): void
    {
        //
    }

    /**
     * Handle the Instructor "force deleted" event.
     */
    public function forceDeleted(Instructor $instructor): void
    {
        //
    }
}
