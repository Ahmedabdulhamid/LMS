<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Auth;

class AuthenticatePanelSession extends AuthenticateSession
{
    public function handle($request, Closure $next)
    {
        Auth::shouldUse(Filament::getAuthGuard());

        return parent::handle($request, $next);
    }
}

