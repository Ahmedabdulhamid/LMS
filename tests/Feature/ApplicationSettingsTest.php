<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\InstructorsPanelProvider;
use App\Providers\Filament\SudentsPanelProvider;
use App\Services\SettingService;
use Database\Seeders\SettingSeeder;
use Filament\Panel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Only this table is needed; no test transaction obscures shared cache behavior.
        (require database_path('migrations/2026_07_16_092432_create_settings_table.php'))->up();
        Cache::flush();
    }

    public function test_settings_are_cached_and_model_changes_invalidate_them(): void
    {
        $this->assertSame('fallback', setting('app_name', 'fallback'));
        $setting = $this->createSetting('app_name', 'School');
        $this->assertSame('School', setting('app_name'));
        DB::enableQueryLog();
        setting('app_name');
        app(SettingService::class)->all();
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();
        $setting->update(['value' => 'New school']);
        $this->assertSame('New school', setting('app_name'));
        $setting->delete();
        $this->assertSame('fallback', setting('app_name', 'fallback'));
    }

    public function test_seeder_preserves_custom_values_and_does_not_duplicate_rows(): void
    {
        $this->seed(SettingSeeder::class);
        Setting::where('key', 'app_name')->firstOrFail()->update(['value' => 'Custom']);
        $this->seed(SettingSeeder::class);
        $this->assertSame(4, Setting::count());
        $this->assertSame('Custom', setting('app_name'));
        $this->assertNull(setting('favicon'));
    }

    public function test_types_groups_and_public_secrets_are_handled(): void
    {
        $this->createSetting('enabled', 'false', 'boolean');
        $this->createSetting('limit', '42', 'integer');
        $this->createSetting('options', '{"a":1}', 'json');
        $this->createSetting('secret', encrypt('private', false))->update(['is_encrypted' => true, 'is_public' => true]);
        $this->assertFalse(setting('enabled'));
        $this->assertSame(42, setting('limit'));
        $this->assertSame(['a' => 1], setting('options'));
        $this->assertArrayNotHasKey('secret', app(SettingService::class)->public()['general']);
        $this->assertSame('private', setting('secret'));
    }

    public function test_urls_and_branding_use_public_disk_and_safe_fallbacks(): void
    {
        config(['filesystems.disks.r2_public.url' => 'https://images.example.test']);
        $this->createSetting('website_logo', 'settings/logo.png', 'file', 'appearance');
        $this->createSetting('favicon', 'settings/favicon.ico', 'file', 'appearance');
        $this->assertSame('https://images.example.test/settings/logo.png', app(SettingService::class)->logoUrl());
        $this->assertSame('https://images.example.test/settings/favicon.ico', app(SettingService::class)->faviconUrl());
        $html = view('partials.application-icons')->render();
        $this->assertStringContainsString('https://images.example.test/settings/favicon.ico', $html);
        $this->assertSame(config('app.name'), app(SettingService::class)->name());
    }

    public function test_missing_table_is_not_cached_and_later_settings_are_visible(): void
    {
        Schema::drop('settings');
        $this->assertSame('fallback', setting('app_name', 'fallback'));
        $this->assertFalse(Cache::has(SettingService::CACHE_KEY));
        (require database_path('migrations/2026_07_16_092432_create_settings_table.php'))->up();
        $this->createSetting('app_name', 'Installed');
        $this->assertSame('Installed', setting('app_name'));
    }

    public function test_all_panels_defer_branding_queries_and_read_updated_settings(): void
    {
        $this->createSetting('app_name', 'My academy');
        foreach ([
            AdminPanelProvider::class,
            InstructorsPanelProvider::class,
            SudentsPanelProvider::class,
        ] as $provider) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $panel = (new $provider($this->app))->panel(Panel::make());
            $this->assertCount(0, DB::getQueryLog());
            DB::disableQueryLog();
            $this->assertSame('My academy', $panel->getBrandName());
            $this->assertSame(asset('images/learning-platform-logo.png'), $panel->getBrandLogo());
            $this->assertNull($panel->getFavicon());
        }
    }

    public function test_cache_clear_and_transaction_rollback_do_not_leave_stale_values(): void
    {
        $setting = $this->createSetting('app_name', 'Original');
        setting('app_name');
        $this->artisan('cache:clear')->assertSuccessful();
        $this->assertFalse(Cache::has(SettingService::CACHE_KEY));
        DB::beginTransaction();
        $setting->update(['value' => 'Uncommitted']);
        $this->assertSame('Uncommitted', setting('app_name'));
        $this->assertFalse(Cache::has(SettingService::CACHE_KEY));
        DB::rollBack();
        $this->assertSame('Original', setting('app_name'));
        DB::transaction(fn () => $setting->fresh()->update(['value' => 'Committed']));
        $this->assertSame('Committed', setting('app_name'));
    }

    private function createSetting(string $key, ?string $value, string $type = 'string', string $group = 'general'): Setting
    {
        return Setting::create(compact('key', 'value', 'type', 'group') + ['is_public' => true, 'is_encrypted' => false]);
    }
}
