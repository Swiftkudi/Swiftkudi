<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutSuspendedUser
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        if ($user && $user->isSuspended()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been suspended. Please contact support.',
                    'redirect' => route('login'),
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Your account has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
