<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerifiedWhenRequired
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null)
    {
        if (!SystemSetting::isEmailVerificationRequired()) {
            return $next($request);
        }

        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                $user->forceFill(['email_verified_at' => now()])->saveQuietly();
            }

            return $next($request);
        }

        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your email address is not verified.'], 403);
        }

        return redirect()->route($redirectToRoute ?: 'verification.notice');
    }
}
