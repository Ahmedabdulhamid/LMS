# R2 originals and Mux streaming

The instructor's existing multipart uploader sends video bytes directly to the private R2 bucket. Saving the lesson dispatches `ProcessCourseVideo` on `video-processing`. The job signs the stored `url` for six hours and sends it to Mux over the API. It never downloads, proxies, or transcodes the video. Mux imports it and supplies adaptive HLS streaming. Original R2 objects and paths are retained during import, success, failure, and source replacement. Explicit lesson/course deletion retains the existing storage deletion behavior.

The public/private disk configuration in `config/filesystems.php` is unchanged. Keep the private bucket private. Preserve the existing browser multipart CORS configuration, including PUT and exposed ETag headers. Images and attachments keep using their existing disk.

## Configuration

Set the following in the server `.env`, then rebuild Laravel's config cache:

```dotenv
MUX_TOKEN_ID=
MUX_TOKEN_SECRET=
MUX_WEBHOOK_SECRET=
MUX_SIGNING_KEY_ID=
MUX_SIGNING_PRIVATE_KEY=
MUX_PLAYBACK_TOKEN_TTL=900
QUEUE_CONNECTION=database
```

Use a Mux Video API token with read/write access and a signing key from the same Mux environment. The private key accepts the base64 value returned by Mux or a quoted PEM with escaped newlines. PHP OpenSSL is required for RS256 signatures. No credentials go into Vite variables or frontend JavaScript.

Set `APP_URL` to the public HTTPS origin. Register **POST `<APP_URL>/webhooks/mux`** in Mux, selecting:

- `video.asset.ready`
- `video.asset.errored`

Use the endpoint's signing secret as `MUX_WEBHOOK_SECRET`. The route is excluded from CSRF verification, but checks the raw body's HMAC-SHA256 signature in `Mux-Signature`, compares signatures in constant time, and rejects timestamps more than five minutes away. Unknown valid event types are acknowledged. Repeated ready events do not resend notifications or change completion time. Old asset events cannot replace the current asset.

The production origin is `https://nexlearn.lt`; the webhook is `https://nexlearn.lt/webhooks/mux`. To print the configured URL:

```bash
php artisan tinker --execute="echo route('mux.webhook');"
```

## Data and playback

Only three Mux columns are introduced: `mux_asset_id`, `mux_playback_id`, and `mux_pending_reference`. The reference correlates webhooks arriving before the import response is persisted and distinguishes replacement uploads. Existing `status`, `processing_error`, `processing_stage`, `processing_progress`, `duration`, and `processed_at` are reused. No signed source URL or playback JWT is stored.

The existing course access service authorizes requests to `/courses/{course}/videos/{video}/playback` before any signing happens. Unauthorized viewers receive 403. Authorized viewers receive short-lived RS256 tokens for HLS (`aud=v`) and thumbnails (`aud=t`). The Shaka player refreshes through that same authorized endpoint before expiry. Playback responses are private and not cacheable. Mux assets request only signed playback policies.

Playback uses Mux only. The abandoned Cloudflare Stream integration and old R2 HLS playlist rewriting have been removed. Existing R2 originals can be imported with `videos:process-pending --legacy`. Old database columns and stored objects are retained; this cleanup does not delete production data. The unused, uncommitted Cloudflare Stream migration was removed; already applied database columns are left intact.

## Deployment

Run in the application root after configuring `.env`:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Build assets locally or in CI and upload `public/build` together with `public/js/r2-multipart-uploader.js`:

```bash
npm ci
npm run build
```

The video queue is still required with an asynchronous queue connection. It performs only short API/database work. On hosting with workers:

```bash
php artisan queue:work --queue=video-processing --tries=3 --timeout=60 --sleep=3
```

On shared hosting, add this cron every minute, using the verified deployment path. `flock` avoids overlapping workers; if unavailable, use the hosting panel's equivalent overlap protection:

```cron
* * * * * cd /home/u891750857/domains/nexlearn.lt/public_html && flock -n storage/framework/mux-worker.lock php artisan queue:work --queue=video-processing --stop-when-empty --max-time=50 --tries=3 --timeout=60 >> storage/logs/mux-worker.log 2>&1
```

Keep the existing default queue worker for ready notifications and existing workers/scheduler for other business tasks. The job timeout must be shorter than the queue connection's `retry_after` (this project configures the database connection to 7800 seconds unless overridden). The host must permit outbound HTTPS to R2 and Mux and public inbound HTTPS for the webhook.

Recovery and optional import of older ready R2 lessons:

```bash
php artisan videos:process-pending
php artisan videos:process-pending --failed
php artisan videos:process-pending --legacy
```

The first command also checks Mux status for already imported processing assets, recovering a missed webhook. `--legacy` puts selected lessons into processing until Mux is ready. Retrying failed imports obtains a fresh source URL. Replaced or explicitly deleted Mux assets are removed through `DeleteMuxVideo` on the same video queue; that job never touches R2. An ambiguous API timeout can leave an unlinked Mux asset; its passthrough reference can be checked in Mux before manually retrying. R2 originals remain available regardless of import outcomes.

Remove these obsolete environment variables: `FFMPEG_PATH`, `FFPROBE_PATH`, `FFMPEG_TIMEOUT`, `FFMPEG_THREADS`, `FFMPEG_PRESET`, `FFMPEG_VIDEO_ENCODER`, `VIDEO_RENDITIONS`. No runtime code reads them. The old metadata/transcoder services remain removed; `ProcessCourseVideo` now contains only orchestration.

## Modified files for this integration

- `app/Jobs/ProcessCourseVideo.php`, `app/Jobs/DeleteMuxVideo.php`, `app/Console/Commands/ProcessPendingCourseVideos.php`, `app/Observers/CourseVideoObserver.php`
- `app/Services/MuxVideoService.php`, `app/Services/MuxVideoLifecycle.php`, `app/Exceptions/MuxVideoException.php`
- `app/Http/Controllers/MuxWebhookController.php`, `app/Http/Controllers/VideoStreamController.php`, `routes/web.php`
- `config/services.php`, `.env.example`, `database/migrations/2026_09_12_180000_add_mux_video_fields.php`
- Restored existing R2 upload functionality in `app/Services/R2VideoUploadService.php`, `app/Http/Controllers/CourseVideoUploadController.php`, `app/Http/Requests/CourseVideos/InitiateCourseVideoUploadRequest.php`, `public/js/r2-multipart-uploader.js`
- `app/Filament/Instructors/Resources/Courses/Schemas/CourseForm.php`, `app/Filament/Instructors/Resources/Courses/Pages/ViewCourse.php`
- `resources/views/filament/forms/components/r2-video-upload.blade.php`, `resources/views/components/course-video-player.blade.php`, `resources/js/video-player.js`
- `tests/Feature/VideoStreamingTest.php`, `tests/Fixtures/openssl.cnf`, `README.md`, this guide

The workspace already contained other uncommitted changes when this integration began. Payments, orders, transactions, Paymob, broadcasting, and bucket configuration were not changed by this integration.

## Verification and references

```bash
php artisan test --compact --filter='VideoStreamingTest|VideoStorageIsolationTest|CourseAccessServiceTest|CourseMediaDeletionTest'
```

Mux requests are mocked in tests. API and signature formats follow [Mux asset creation](https://www.mux.com/docs/api-reference/video/assets/create-asset), [webhook verification](https://www.mux.com/docs/core/verify-webhook-signatures), and [signed playback](https://www.mux.com/docs/guides/secure-video-playback).

The scheduler cron runs every minute:

```cron
* * * * * cd /home/u891750857/domains/nexlearn.lt/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Production uses an additional entry point and copied assets in the project root. Preserve its `index.php`, `.htaccess`, `.env`, and modified seeder. Deploy the locally built assets to both `public/build` and root `build`, and synchronize tracked public JS/CSS without deleting production-only files. Back up those directories before updating them.
