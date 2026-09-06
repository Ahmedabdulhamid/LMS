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
use App\Filament\Student\Pages\Dashboard;
use App\Filament\Student\Pages\Profile;
use App\Filament\Student\Resources\CourseResource;
use App\Filament\Student\Resources\PaymentResource;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationItem;
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
            ->profile(Profile::class, isSimple: false)
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
            ->resources([
                CourseResource::class,
                PaymentResource::class,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([])
            ->navigationItems([
                NavigationItem::make(fn (): string => __('student-panel.navigation.profile'))
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => route('filament.students.auth.profile'))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.students.auth.profile'))
                    ->sort(4),
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
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn () => view('student-sidebar-logout'),
            );
    }
}
