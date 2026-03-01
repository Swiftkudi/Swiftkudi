<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogoutOnInactivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = (int) config('session.inactivity_timeout', config('session.lifetime', 120));
            $maxIdleSeconds = max(1, $timeoutMinutes) * 60;
            $lastActivityAt = (int) $request->session()->get('last_activity_at', now()->timestamp);

            if ((now()->timestamp - $lastActivityAt) > $maxIdleSeconds) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with(
                    'warning',
                    'Your session timed out due to inactivity. Please sign in again to continue securely.'
                );
            }

            $request->session()->put('last_activity_at', now()->timestamp);
        }

        return $next($request);
    }
}
