<?php

namespace App\Providers\Filament;

use App\Filament\Instructors\Pages\Auth\EditProfile;
use App\Http\Middleware\AuthenticatePanelSession;
use App\Http\Middleware\EnsurePanelUserType;
use App\Http\Middleware\SetLocale;
use App\Services\Register as ServicesRegister;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class InstructorsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('instructors')
            ->path('instructors')
            ->viteTheme('resources/css/filament/instructors/theme.css')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->registration(ServicesRegister::class)
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->authGuard('instructor')
            ->darkMode()
            ->userMenu()
            ->defaultThemeMode(ThemeMode::Dark)
            ->brandLogo(asset('images/learning-platform-logo.png'))
            ->darkModeBrandLogo(asset('images/learning-platform-logo.png'))
            ->brandLogoHeight('3rem')

            ->discoverResources(in: app_path('Filament/Instructors/Resources'), for: 'App\Filament\Instructors\Resources')
            ->discoverPages(in: app_path('Filament/Instructors/Pages'), for: 'App\Filament\Instructors\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Instructors/Widgets'), for: 'App\Filament\Instructors\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                SetLocale::class,
                AuthenticatePanelSession::class,
                // AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsurePanelUserType::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn () => view('filament.components.language-switcher', ['floating' => false]),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn () => view('filament.components.language-switcher', ['floating' => true]),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.components.r2-uploader-script'),
            );
    }
}
