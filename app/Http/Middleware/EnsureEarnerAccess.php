<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;

class EnsureEarnerAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        // Allow buyers, admins, and non-earner types through without earner checks
        if (!$user || !in_array($user->account_type, ['earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer']) || $user->is_admin) {
            return $next($request);
        }

        $wallet = $user->wallet;
        $isActivated = $wallet && $wallet->is_activated;

        // Earner activation fee must be paid first
        if ($user->account_type === 'earner' && !$isActivated) {
            $routeName = $request->route() ? $request->route()->getName() : null;
            $onboardingAllowed = [
                'onboarding.select',
                'onboarding.select.post',
                'onboarding.earn.activate',
                'onboarding.earn.referral.complete',
                'onboarding.earn.referral.skip',
                'onboarding.earn.feature.unlock',
                'onboarding.task-creator',
                'onboarding.freelancer',
                'onboarding.digital-product',
                'onboarding.growth',
                'onboarding.buyer',
                'start-your-journey',
                'start-journey.apply-bundle',
                'start-journey.check-status',
                'start-journey.unlock-success',
                'wallet.deposit',
                'wallet.process-deposit',
                'payment.initialize',
                'payment.callback',
                'wallet.activate',
                'wallet.activate.process',
                'wallet.activate.skip',
            ];

            if (in_array($routeName, $onboardingAllowed)) {
                return $next($request);
            }

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please pay the earnings activation fee to unlock worker tools.',
                    'redirect' => route('start-your-journey'),
                ], 403);
            }

            return redirect()->route('start-your-journey')
                ->with('warning', 'Please pay the earnings activation fee to unlock worker tools.');
        }

        // Freelancer must complete profile and first service
        if ($user->account_type === 'freelancer') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            $profileAllowed = [
                'professional-services.edit-profile',
                'professional-services.update-profile',
                'onboarding.freelancer',
                'dashboard',
                'logout',
            ];

            if (!$user->freelancer_profile_completed && !in_array($routeName, $profileAllowed)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Complete your freelancer profile before browsing professional services.',
                        'redirect' => route('professional-services.edit-profile'),
                    ], 403);
                }
                return redirect()->route('professional-services.edit-profile')
                    ->with('warning', 'Complete your freelancer profile before browsing professional services.');
            }

            $serviceAllowed = array_merge($profileAllowed, [
                'professional-services.create',
                'professional-services.store',
                'professional-services.my-services',
            ]);

            if (!$user->freelancer_service_created && !in_array($routeName, $serviceAllowed)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Create your first professional service to unlock marketplace browsing.',
                        'redirect' => route('professional-services.create'),
                    ], 403);
                }
                return redirect()->route('professional-services.create')
                    ->with('warning', 'Create your first professional service to unlock marketplace browsing.');
            }
        }

        // Digital product sellers must upload first product before broader access
        if ($user->account_type === 'digital_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            $digitalAllowed = [
                'digital-products.create',
                'digital-products.store',
                'digital-products.my-products',
                'onboarding.digital-product',
                'dashboard',
                'logout',
                'wallet.deposit',
                'wallet.process-deposit',
                'payment.initialize',
                'payment.callback',
                'wallet.activate',
                'wallet.activate.process',
                'wallet.activate.skip',
            ];

            if (!$isActivated) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please activate your wallet to start selling digital products.',
                        'redirect' => route('wallet.activate'),
                    ], 403);
                }
                return redirect()->route('wallet.activate')
                    ->with('warning', 'Please activate your wallet to start selling digital products.');
            }

            if (!$user->digital_product_uploaded && !in_array($routeName, $digitalAllowed)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Upload your first digital product to unlock the marketplace.',
                        'redirect' => route('digital-products.create'),
                    ], 403);
                }
                return redirect()->route('digital-products.create')
                    ->with('warning', 'Upload your first digital product to unlock the marketplace.');
            }
        }

        // Growth sellers must create first listing before access
        if ($user->account_type === 'growth_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            $growthAllowed = [
                'growth.create',
                'growth.store',
                'growth.my-listings',
                'onboarding.growth',
                'dashboard',
                'logout',
                'wallet.deposit',
                'wallet.process-deposit',
                'payment.initialize',
                'payment.callback',
                'wallet.activate',
                'wallet.activate.process',
                'wallet.activate.skip',
            ];

            if (!$isActivated) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please activate your wallet to start listing on growth marketplace.',
                        'redirect' => route('wallet.activate'),
                    ], 403);
                }
                return redirect()->route('wallet.activate')
                    ->with('warning', 'Please activate your wallet to start listing on growth marketplace.');
            }

            if (!$user->growth_listing_created && !in_array($routeName, $growthAllowed)) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Create your first growth listing to unlock the marketplace.',
                        'redirect' => route('growth.create'),
                    ], 403);
                }
                return redirect()->route('growth.create')
                    ->with('warning', 'Create your first growth listing to unlock the marketplace.');
            }
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        $featureRequired = null;

        if ($routeName && str_starts_with($routeName, 'professional-services')) {
            $featureRequired = 'freelance_marketplace';
        } elseif ($routeName && str_starts_with($routeName, 'growth.')) {
            $featureRequired = 'growth_marketplace';
        } elseif ($routeName && str_starts_with($routeName, 'digital-products')) {
            $featureRequired = 'digital_products';
        } elseif ($routeName && str_starts_with($routeName, 'tasks.create')) {
            $featureRequired = 'task_creation';
        }

        if ($featureRequired && $user->hasTaskCreatorFeature($featureRequired)) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unlock ' . str_replace('_', ' ', $featureRequired) . ' to access this section.',
                    'redirect' => route('start-your-journey'),
                ], 403);
            }
            return redirect()->route('start-your-journey')
                ->with('warning', 'Unlock ' . str_replace('_', ' ', $featureRequired) . ' to access this section.');
        }

        // Buyer feature access checks
        if ($user->account_type === 'buyer') {
            $routeName = $request->route() ? $request->route()->getName() : null;

            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasBuyerFeature('professional_services')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock professional services to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasBuyerFeature('task_creation')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock task creation to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }

            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasBuyerFeature('available_tasks')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock available tasks to browse tasks.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
        }

        // Task Creator feature access checks
        if ($user->account_type === 'task_creator') {
            $routeName = $request->route() ? $request->route()->getName() : null;

            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasTaskCreatorFeature('available_tasks')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock available tasks to browse tasks.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }

            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasTaskCreatorFeature('professional_services')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock professional services to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasTaskCreatorFeature('growth_listings')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock growth listings to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasTaskCreatorFeature('digital_products')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock digital products to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        // Freelancer feature access checks
        if ($user->account_type === 'freelancer') {
            $routeName = $request->route() ? $request->route()->getName() : null;

            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasFreelancerFeature('task_creation')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock task creation to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }

            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasFreelancerFeature('available_tasks')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock available tasks to browse tasks.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }

            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasFreelancerFeature('growth_listings')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock growth listings to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasFreelancerFeature('digital_products')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock digital products to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        // Digital Seller feature access checks
        if ($user->account_type === 'digital_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;

            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasDigitalSellerFeature('task_creation')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock task creation to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }

            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasDigitalSellerFeature('available_tasks')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock available tasks to browse tasks.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }

            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasDigitalSellerFeature('professional_services')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock professional services to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasDigitalSellerFeature('growth_listings')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock growth listings to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }
        }

        // Growth Seller feature access checks
        if ($user->account_type === 'growth_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;

            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasGrowthSellerFeature('task_creation')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock task creation to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }

            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasGrowthSellerFeature('available_tasks')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock available tasks to browse tasks.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }

            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasGrowthSellerFeature('professional_services')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock professional services to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }

            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasGrowthSellerFeature('digital_products')) {
                if ($isAjax) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unlock digital products to access this feature.',
                        'redirect' => route('dashboard'),
                    ], 403);
                }
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        return $next($request);
    }
}
