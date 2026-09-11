# Application settings

Manage branding in the existing admin Settings resource (`/admin/settings`). Run
`php artisan db:seed --class=SettingSeeder` to add missing defaults. Re-running it
preserves values already configured by administrators.

| Key | Group | Type |
| --- | --- | --- |
| `app_name` | `general` | `string` |
| `website_logo` | `appearance` | `file` |
| `website_icon` | `appearance` | `file` |
| `favicon` | `appearance` | `file` |

Image uploads use the existing `lms-upload.disk` (`r2_public`) with generated
filenames. Configure that bucket's browser upload CORS as for other public images.
Stored values are object paths; the settings service builds their public URLs.
The icon is used as an Apple touch icon and as a fallback when no favicon is set.

```php
setting('app_name', config('app.name'));
setting('website_logo'); // Stored object path
setting_image_url('website_logo'); // Public URL, or null
setting('enabled', false, 'integrations'); // Explicit group for other settings

$settings = app(\App\Services\SettingService::class);
$settings->name(); // Nonblank application name, with config fallback
$settings->logoUrl(); // Falls back to the existing bundled logo
$settings->all(); // Typed values grouped by group; server-side use only
$settings->public(); // Excludes encrypted and nonpublic settings
$settings->forget();
```

Helpers load in `AppServiceProvider::register()` without database access.
Panel branding uses lazy closures; frontend documents share the icon partial.
No environment variables or application configuration values are overwritten.

All rows share `SettingService::CACHE_KEY` in Laravel's default cache store.
Eloquent saves/deletes invalidate the cache immediately and again after commit.
Reads within transactions bypass shared caching, so rollback cannot publish
uncommitted values. `php artisan cache:clear` clears this cache normally.
Use a persistent cache store in deployment, rather than the testing `array` store.

Update settings through model instances (`$setting->update(...)`, `$setting->delete()`)
so Eloquent events run. Raw SQL, query-builder bulk writes and `withoutEvents()`
intentionally bypass model events; callers using those must call `forget()` after
commit. The default settings seeder runs with events enabled.

If the database/table is unavailable, reads return fallbacks without caching the
failure. Missing rows or null image values are supported. Types include strings,
text, files, boolean, integer and JSON; existing encrypted settings remain supported.

Validation: `php artisan test --filter=ApplicationSettingsTest`.
