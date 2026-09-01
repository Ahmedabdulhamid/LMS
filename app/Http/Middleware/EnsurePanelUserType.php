<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\Instructor;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelUserType
{
    /** @var array<string, array{guard: string, model: class-string, active: bool}> */
    private const PANEL_ACCESS = [
        'admin' => ['guard' => 'admin', 'model' => Admin::class, 'active' => false],
        'instructors' => ['guard' => 'instructor', 'model' => Instructor::class, 'active' => true],
        'students' => ['guard' => 'student', 'model' => User::class, 'active' => true],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentOrDefaultPanel();
        $access = self::PANEL_ACCESS[$panel->getId()] ?? null;

        abort_if($access === null, 403, __('lms.auth.forbidden_panel'));
        abort_if($panel->getAuthGuard() !== $access['guard'], 403, __('lms.auth.forbidden_panel'));

        $user = auth($access['guard'])->user();

        abort_unless($user instanceof $access['model'], 403, __('lms.auth.forbidden_panel'));
        abort_if($access['active'] && ! $user->is_active, 403, __('lms.auth.inactive'));

        return $next($request);
    }
}
