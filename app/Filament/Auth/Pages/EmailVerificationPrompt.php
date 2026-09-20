<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt as BaseEmailVerificationPrompt;
use Illuminate\Contracts\Support\Htmlable;

class EmailVerificationPrompt extends BaseEmailVerificationPrompt
{
    protected string $view = 'filament.auth.pages.email-verification-prompt';

    public function getTitle(): string|Htmlable
    {
        return __('email-verification.page.title');
    }

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
