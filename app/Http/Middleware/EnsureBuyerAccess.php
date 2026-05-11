<?php

namespace App\Http\Middleware;

use App\Services\OnboardingSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBuyerAccess
{
    /**
     * Handle an incoming request.
     *
     * Ensures buyers have completed onboarding requirements before accessing marketplace.
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
            // Check if buyer onboarding is enabled
            if (!OnboardingSettingsService::isBuyerOnboardingEnabled()) {
                return $next($request); // Allow access if onboarding is disabled
            }

            // Get buyer requirements
            $requirements = OnboardingSettingsService::getOnboardingRequirements('buyer');

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
                // Allow wallet and payment routes for funding
                'wallet.index',
                'wallet.deposit',
                'wallet.process-deposit',
                'payment.initialize',
                'payment.callback',
                'wallet.activate',
                'wallet.activate.process',
                'wallet.activate.skip',
                'wallet.transactions',
            ];

            $current = ltrim($request->path(), '/');

            foreach ($allowed as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }

            // Check if category selection is required and completed
            if ($requirements['category_selection_required'] && !$user->buyer_onboarding_completed) {
                return redirect()->route('onboarding.buyer.categories')
                    ->with('warning', 'Please select your preferred categories to continue browsing.');
            }
        }

        return $next($request);
    }
}
