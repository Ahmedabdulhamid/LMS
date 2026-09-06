<?php

namespace App\Filament\Student\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

class Logout extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $routePath = 'sign-out';

    protected string $view = 'filament.students.pages.logout';

    public function mount(): void
    {
        Filament::auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(Filament::getLoginUrl(), navigate: false);
    }
}
