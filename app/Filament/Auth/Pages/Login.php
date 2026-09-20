<?php

namespace App\Filament\Auth\Pages;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()->hint(null);
    }
}
