<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Central service for onboarding gating and feature access control.
 */
class AccessGateService
{
    public function normalizeFeatureKey(string $feature): string
    {
        $aliases = config('features.feature_aliases', []);
        return $aliases[$feature] ?? $feature;
    }

    public function getFeatureConfig(string $feature): ?array
    {
        $feature = $this->normalizeFeatureKey($feature);
        return config("features.features.{$feature}");
    }

     public function checkFeatureAccess(User $user, string $feature, ?string $context = null): array
     {
         // Admins have unlimited access to all features
         if ($user->isAdmin()) {
             return [
                 'can_access' => true,
                 'reason' => 'admin_privileges',
                 'unlock_path' => null,
                 'unlock_message' => null,
                 'unlock_action' => null,
                 'context_message' => 'Admin access granted',
             ];
         }

         $feature = $this->normalizeFeatureKey($feature);
         $featureConfig = $this->getFeatureConfig($feature);

         if (!$featureConfig) {
             return [
                 'can_access' => false,
                 'reason' => 'unknown_feature',
                 'unlock_path' => 'dashboard',
                 'unlock_message' => 'Feature not available.',
                 'unlock_action' => 'Contact Support',
                 'context_message' => 'Unknown feature.',
             ];
         }

         $accountType = $user->account_type;
         $allowedRoles = $featureConfig['allowed_roles'] ?? [];

         if (!in_array($accountType, $allowedRoles, true)) {
             return [
                 'can_access' => false,
                 'reason' => 'role_restricted',
                 'unlock_path' => $featureConfig['unlock_route'],
                 'unlock_message' => $featureConfig['unlock_message'],
                 'unlock_action' => $featureConfig['unlock_action'],
                 'context_message' => $featureConfig['context_message'],
             ];
         }

         if (in_array($accountType, $featureConfig['always_enabled_for_roles'] ?? [], true)) {
             return [
                 'can_access' => true,
                 'reason' => 'granted',
                 'unlock_path' => null,
                 'unlock_message' => null,
                 'unlock_action' => null,
                 'context_message' => $featureConfig['context_message'],
             ];
         }

         if (!$user->hasFeatureAccess($feature)) {
             return [
                 'can_access' => false,
                 'reason' => 'feature_locked',
                 'unlock_path' => $featureConfig['unlock_route'],
                 'unlock_message' => $featureConfig['unlock_message'],
                 'unlock_action' => $featureConfig['unlock_action'],
                 'context_message' => $featureConfig['context_message'],
             ];
         }

         return [
             'can_access' => true,
             'reason' => 'granted',
             'unlock_path' => null,
             'unlock_message' => null,
             'unlock_action' => null,
             'context_message' => $featureConfig['context_message'],
         ];
     }

    public function handleAccessGate(Request $request, string $feature, ?string $context = null)
    {
        $user = $request->user();
        $isAjax = $request->expectsJson() || $request->ajax();

        if (!$user) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required.',
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login');
        }

        $accessCheck = $this->checkFeatureAccess($user, $feature, $context);

        if ($accessCheck['can_access']) {
            return null;
        }

        $this->logRestrictedAccess($user, $feature, $context, $accessCheck['reason']);

        // Prepare unlock prompt data for session (used by unlock pages)
        $unlockPrompt = [
            'feature' => $feature,
            'message' => $accessCheck['unlock_message'],
            'action' => $accessCheck['unlock_action'],
            'context' => $context,
        ];

        if ($isAjax) {
            // Flash unlock_prompt to session for the next request (the redirect)
            $request->session()->flash('unlock_prompt', $unlockPrompt);

            return response()->json([
                'success' => false,
                'message' => $accessCheck['unlock_message'] ?? 'Feature access restricted.',
                'redirect' => route($accessCheck['unlock_path']),
                'unlock_prompt' => $unlockPrompt,
            ], 403);
        }

        return redirect()->route($accessCheck['unlock_path'])
            ->with('unlock_prompt', $unlockPrompt);
    }

    public function getUnlockModalData(User $user, string $feature, ?string $context = null): array
    {
        $accessCheck = $this->checkFeatureAccess($user, $feature, $context);

        return [
            'show_modal' => !$accessCheck['can_access'],
            'feature' => $feature,
            'title' => 'Unlock ' . ucfirst(str_replace('_', ' ', $feature)),
            'message' => $accessCheck['unlock_message'],
            'action_text' => $accessCheck['unlock_action'],
            'redirect_url' => $accessCheck['unlock_path'] ? route($accessCheck['unlock_path']) : null,
            'context_message' => $accessCheck['context_message'],
        ];
    }

    private function logRestrictedAccess(User $user, string $feature, ?string $context, string $reason): void
    {
        Log::info('Feature access restricted', [
            'user_id' => $user->id,
            'account_type' => $user->account_type,
            'feature' => $feature,
            'context' => $context,
            'reason' => $reason,
            'timestamp' => now()->toISOString(),
        ]);
    }

    public function getOnboardingRedirect(User $user): ?array
    {
        if (!$user->account_type) {
            return [
                'route' => 'onboarding.select',
                'message' => 'Select an account type to continue.',
            ];
        }

        // Use OnboardingSettingsService for centralized logic
        $nextStep = \App\Services\OnboardingSettingsService::getNextOnboardingStep($user);

        if ($nextStep) {
            return [
                'route' => $nextStep['route'],
                'message' => $nextStep['message'],
            ];
        }

        return null;
    }

    public function getNextOnboardingStep(User $user): ?array
    {
        if (!$user->account_type) {
            return null;
        }

        // Use OnboardingSettingsService for centralized logic
        return \App\Services\OnboardingSettingsService::getNextOnboardingStep($user);
    }

    public function isOnboardingComplete(User $user): bool
    {
        if (!$user->account_type) {
            return false;
        }

        // Use OnboardingSettingsService for centralized logic
        return \App\Services\OnboardingSettingsService::hasUserCompletedOnboarding($user);
    }

    public function isOnboardingAllowedRequest(Request $request): bool
    {
        $routeName = $request->route() ? $request->route()->getName() : null;

        if (!$routeName) {
            return false;
        }

         $allowedRoutes = [
             'onboarding.select',
             'onboarding.select.post',
             'onboarding.earner-type',
             'onboarding.earn.activate',
             'onboarding.earn.referral.complete',
             'onboarding.earn.referral.skip',
             'onboarding.earn.feature.unlock',
             'onboarding.feature.unlock.complete',
             'onboarding.feature.unlock.page',
             'onboarding.freelancer.activate',
             'onboarding.digital-product.activate',
             'onboarding.growth.activate',
             'onboarding.buyer.feature.unlock',
             'onboarding.task-creator.feature.unlock',
             'onboarding.freelancer.feature.unlock',
             'onboarding.digital-seller.feature.unlock',
             'onboarding.growth-seller.feature.unlock',
             'onboarding.features',
            'onboarding.task-creator',
            'onboarding.freelancer',
            'onboarding.digital-product',
            'onboarding.growth',
            'onboarding.buyer',
            'onboarding.buyer.categories',
            'settings.buyer-categories',
            'settings.buyer-categories.update',
            'start-your-journey',
            'start-journey.apply-bundle',
            'start-journey.check-status',
            'start-journey.unlock-success',
            'wallet.deposit',
            'wallet.process-deposit',
            'wallet.activate',
            'wallet.activate.process',
            'wallet.activate.skip',
            // Task routes that should not cause redirect loops
            'tasks.create.new',
            'tasks.create',
            'tasks.create.store',
            'tasks.create.save-draft',
            'tasks.create.import',
            'tasks.create.import.search',
            'tasks.create.import.do',
            'tasks.create.import.task-types',
            'unlock-access',
            'payment.initialize',
            'payment.callback',
            'health',
            'login',
            'logout',
            'verification.notice',
            'verification.verify',
        ];

        if (in_array($routeName, $allowedRoutes, true)) {
            return true;
        }

        if ($request->is('onboarding/*') || $request->is('api/*') || $request->is('health') || $request->is('webhooks/*')) {
            return true;
        }

        return false;
    }
}
