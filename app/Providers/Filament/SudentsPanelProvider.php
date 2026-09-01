<?php

namespace App\Providers\Filament;

use App\Filament\Sudents\Pages\Auth\EditProfile;
use App\Filament\Sudents\Pages\Auth\Register;
use App\Http\Middleware\AuthenticatePanelSession;
use App\Http\Middleware\EnsurePanelUserType;
use App\Http\Middleware\SetLocale;
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

class SudentsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('students')
            ->path('students')
            ->viteTheme('resources/css/filament/students/theme.css')
            ->login()
            ->registration(Register::class)
             ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->authGuard('student')
            ->darkMode()
            ->userMenu()
            ->brandLogo(asset('images/learning-platform-logo.png'))
            ->darkModeBrandLogo(asset('images/learning-platform-logo.png'))
            ->brandLogoHeight('3rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverResources(in: app_path('Filament/Sudents/Resources'), for: 'App\Filament\Sudents\Resources')
            ->discoverPages(in: app_path('Filament/Sudents/Pages'), for: 'App\Filament\Sudents\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Sudents/Widgets'), for: 'App\Filament\Sudents\Widgets')
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
                //AuthenticateSession::class,
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
            );
    }
}
