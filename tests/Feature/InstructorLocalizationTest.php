<?php

namespace Tests\Feature;

use App\Filament\Instructors\Pages\Dashboard;
use App\Filament\Instructors\Resources\Courses\CourseResource;
use App\Filament\Instructors\Resources\Payments\PaymentResource;
use App\Filament\Instructors\Resources\Reviews\ReviewResource;
use App\Filament\Instructors\Resources\Students\StudentResource;
use Tests\TestCase;

class InstructorLocalizationTest extends TestCase
{
    public function test_instructor_navigation_is_translated_in_english_and_arabic(): void
    {
        $expected = [
            'en' => ['Dashboard', 'Courses', 'Students', 'Payments', 'Reviews'],
            'ar' => ['لوحة التحكم', 'الدورات', 'الطلاب', 'المدفوعات', 'التقييمات'],
        ];

        foreach ($expected as $locale => $labels) {
            app()->setLocale($locale);

            $this->assertSame($labels, [
                Dashboard::getNavigationLabel(),
                CourseResource::getNavigationLabel(),
                StudentResource::getNavigationLabel(),
                PaymentResource::getNavigationLabel(),
                ReviewResource::getNavigationLabel(),
            ]);
        }
    }

    public function test_filament_layout_direction_follows_the_locale(): void
    {
        app()->setLocale('en');
        $this->assertSame('ltr', __('filament-panels::layout.direction'));

        app()->setLocale('ar');
        $this->assertSame('rtl', __('filament-panels::layout.direction'));
    }

    public function test_locale_switcher_persists_the_selected_locale(): void
    {
        $this->from('/instructors')->get(route('locale.switch', 'ar'))
            ->assertRedirect('/instructors')
            ->assertSessionHas('locale', 'ar');

        $this->from('/instructors')->get(route('locale.switch', 'en'))
            ->assertRedirect('/instructors')
            ->assertSessionHas('locale', 'en');
    }
}
