<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAuthenticationDebug
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log incoming request details
        Log::debug('Authentication Debug', [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'url' => $request->url(),
            'scheme' => $request->getScheme(),
            'host' => $request->getHost(),
            'is_secure' => $request->isSecure(),
            'has_session_cookie' => $request->hasCookie(config('session.cookie')),
            'session_id' => $request->getSession()?->getId(),
        ]);

        // Log proxy headers if present
        if ($request->header('X-Forwarded-For') || $request->header('CF-Connecting-IP')) {
            Log::debug('Proxy Headers Detected', [
                'x_forwarded_for' => $request->header('X-Forwarded-For'),
                'cf_connecting_ip' => $request->header('CF-Connecting-IP'),
                'x_forwarded_proto' => $request->header('X-Forwarded-Proto'),
                'x_forwarded_host' => $request->header('X-Forwarded-Host'),
                'x_forwarded_port' => $request->header('X-Forwarded-Port'),
            ]);
        }

        // Log client IP
        Log::debug('Request IP', [
            'client_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $response = $next($request);

        // Log authentication state after request processing
        Log::debug('Authentication State After Request', [
            'authenticated' => Auth::check(),
            'guard' => Auth::getDefaultDriver(),
            'user_id' => Auth::id(),
            'student_authenticated' => Auth::guard('student')->check(),
            'student_id' => Auth::guard('student')->id(),
            'response_status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
