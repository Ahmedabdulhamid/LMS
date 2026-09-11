@if ($faviconUrl = app(\App\Services\SettingService::class)->faviconUrl())
    <link rel="icon" href="{{ $faviconUrl }}">
@endif
@if ($websiteIconUrl = setting_image_url('website_icon'))
    <link rel="apple-touch-icon" href="{{ $websiteIconUrl }}">
@endif
