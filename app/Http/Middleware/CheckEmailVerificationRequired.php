<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;

class CheckEmailVerificationRequired
{
    /**
     * Handle an incoming request.
     * Allow access if:
     * 1. Email verification is disabled in admin settings
     * 2. User is admin/superuser
     * 3. User registered via Google
     * 4. User has verified email
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        $isAjax = $request->expectsJson() || $request->ajax();

        if ($user === null) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect('/login');
        }

        // Check if email verification is globally disabled in admin settings
        $verificationRequired = SystemSetting::isEmailVerificationRequired();

        if (!$verificationRequired) {
            return $next($request);
        }

        // Skip verification for admins
        if ($user->is_admin || !is_null($user->admin_role_id)) {
            return $next($request);
        }

        // Skip verification for Google OAuth users
        if (!empty($user->google_id)) {
            return $next($request);
        }

        // Otherwise, require email verification
        if (!$user->hasVerifiedEmail()) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email verification required.',
                    'redirect' => route('verification.notice'),
                ], 403);
            }
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}

