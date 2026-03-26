<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBuyerAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensures buyers have completed category selection before accessing marketplace.
     * Non-buyers are allowed through without checks.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Skip checks for admins and suspended users
        if ($user->is_admin || $user->is_suspended) {
            return $next($request);
        }

        // Only apply checks to buyers
        if ($user->account_type === 'buyer') {
            // Allowed routes during buyer onboarding
            $allowed = [
                'onboarding*',
                'logout',
                'password*',
                'verification*',
                'api/*',
                'health',
                'webhook*',
                'dashboard',
                'settings*',
            ];

            $current = ltrim($request->path(), '/');

            foreach ($allowed as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }

            // Check if buyer has completed category selection
            if (!$user->buyer_onboarding_completed) {
                return redirect()->route('onboarding.buyer.categories')
                    ->with('warning', 'Please select your preferred categories to continue browsing.');
            }
        }

        return $next($request);
    }
}
