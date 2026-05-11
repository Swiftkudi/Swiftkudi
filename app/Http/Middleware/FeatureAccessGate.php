<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AccessGateService;

/**
 * Middleware to handle feature access with guided unlock flows.
 * Replaces hard restrictions with conversion-focused redirects.
 */
class FeatureAccessGate
{
    protected AccessGateService $accessGateService;

    public function __construct(AccessGateService $accessGateService)
    {
        $this->accessGateService = $accessGateService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature, ?string $context = null)
    {
        $redirect = $this->accessGateService->handleAccessGate($request, $feature, $context);

        if ($redirect) {
            return $redirect;
        }

        return $next($request);
    }
}