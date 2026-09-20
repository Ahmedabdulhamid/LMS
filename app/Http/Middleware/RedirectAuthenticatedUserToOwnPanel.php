<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedUserToOwnPanel
{
    /** @var array<string, array{guard: string, dashboard: string}> */
    private const PANELS = [
        'admin' => ['guard' => 'admin', 'dashboard' => 'filament.admin.pages.dashboard'],
        'instructors' => ['guard' => 'instructor', 'dashboard' => 'filament.instructors.pages.dashboard'],
        'students' => ['guard' => 'student', 'dashboard' => 'filament.students.pages.dashboard'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $target = $this->targetPanel($request);

        if ($target === null || auth(self::PANELS[$target]['guard'])->check()) {
            return $next($request);
        }

        foreach (self::PANELS as $panel => $config) {
            if ($panel !== $target && auth($config['guard'])->check()) {
                return redirect()->route($config['dashboard']);
            }
        }

        return $next($request);
    }

    private function targetPanel(Request $request): ?string
    {
        $segment = $request->segment(1);

        return match ($segment) {
            'admin' => 'admin',
            'instructor', 'instructors' => 'instructors',
            'student', 'students' => 'students',
            default => null,
        };
    }
}
