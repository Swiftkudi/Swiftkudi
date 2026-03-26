<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // If admin or suspended, do not force onboarding
        if ($user->is_admin || $user->is_suspended) {
            return $next($request);
        }

        // Excluded paths while onboarding
        $allowed = [
            'onboarding/select',
            'onboarding/*',
            'logout',
            'password*',
            'verification*',
            'api/*',
            'health',
            'webhook*',
        ];

        $current = ltrim($request->path(), '/');

        foreach ($allowed as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.select');
        }

        return $next($request);
    }
}
