<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_arabic_faq_form_keys_are_translated(): void
    {
        app()->setLocale('ar');
        $this->assertSame('السؤال', __('lms.faqs.question'), lang_path('ar/lms.php'));
        $admin = Admin::query()->create([
            'name' => 'Admin', 'email' => 'faq-admin@example.com', 'password' => 'Password123!',
        ]);

        $this->actingAs($admin, 'admin')->withSession(['locale' => 'ar'])
            ->get(route('filament.admin.resources.faqs.create'))
            ->assertOk()
            ->assertSee('السؤال')
            ->assertSee('الإجابة')
            ->assertDontSee('lms.faqs.question')
            ->assertDontSee('lms.faqs.fields.question');
    }
}
