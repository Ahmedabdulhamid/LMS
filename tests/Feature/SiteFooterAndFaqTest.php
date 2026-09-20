<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteFooterAndFaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_panel_pages_share_the_cached_settings_brand_footer(): void
    {
        Setting::query()->create([
            'group' => 'general', 'key' => 'app_name', 'value' => 'NexLearn Academy',
            'type' => 'string', 'is_public' => true, 'is_encrypted' => false,
        ]);
        app(SettingService::class)->forget();

        $this->get(route('home'))->assertOk()
            ->assertSee('NexLearn Academy')
            ->assertSee(route('faqs.index'), false);

        $this->get(route('filament.students.auth.login'))->assertOk()
            ->assertSee('NexLearn Academy')
            ->assertSee(route('faqs.index'), false);

        $this->get(route('filament.admin.auth.login'))->assertOk()
            ->assertSee('NexLearn Academy')
            ->assertSee(route('faqs.index'), false);
    }

    public function test_faq_page_is_public_and_cache_is_invalidated_when_an_answer_changes(): void
    {
        $faq = Faq::query()->create(['question' => 'How do subscriptions work?', 'answer' => 'Original answer']);

        $this->get(route('faqs.index'))->assertOk()
            ->assertSee($faq->question)
            ->assertSee('Original answer');

        $faq->update(['answer' => 'Updated answer']);

        $this->get(route('faqs.index'))->assertOk()
            ->assertSee('Updated answer')
            ->assertDontSee('Original answer');
    }
}
