<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AccessGateService;

class EnsureOnboardingCompleted
{
    protected AccessGateService $accessGateService;

    public function __construct(AccessGateService $accessGateService)
    {
        $this->accessGateService = $accessGateService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        if (!$user) {
            return $next($request);
        }

        if ($user->is_admin || $user->is_suspended) {
            return $next($request);
        }

        if ($this->accessGateService->isOnboardingAllowedRequest($request)) {
            return $next($request);
        }

        $redirect = $this->accessGateService->getOnboardingRedirect($user);

        if ($redirect) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $redirect['message'] ?? 'Onboarding required.',
                    'redirect' => route($redirect['route']),
                ], 403);
            }
            return redirect()->route($redirect['route'])->with('warning', $redirect['message']);
        }

        return $next($request);
    }
}
