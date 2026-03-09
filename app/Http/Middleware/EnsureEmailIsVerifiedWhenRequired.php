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

        if (method_exists($request->user(), 'hasVerifiedEmail') && $request->user()->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your email address is not verified.'], 403);
        }

        return redirect()->route($redirectToRoute ?: 'verification.notice');
    }
}
