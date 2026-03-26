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

        $wallet = $user->wallet;

        // Check activation status
        $isActivated = $wallet && $wallet->is_activated;

        // Allow buyers, admins, and non-earner types through without earner checks
        if (!$user || !in_array($user->account_type, ['earner', 'task_creator', 'freelancer', 'digital_seller', 'growth_seller', 'buyer']) || $user->is_admin) {
            return $next($request);
        }

        // Earner activation fee must be paid first
        if ($user->account_type === 'earner' && !$user->activation_paid) {
            $routeName = $request->route() ? $request->route()->getName() : null;
            // Allow onboarding routes to proceed
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
            ];
            
            if (in_array($routeName, $onboardingAllowed)) {
                return $next($request);
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
                return redirect()->route('professional-services.edit-profile')
                    ->with('warning', 'Complete your freelancer profile before browsing professional services.');
            }

            $serviceAllowed = array_merge($profileAllowed, [
                'professional-services.create',
                'professional-services.store',
                'professional-services.my-services',
            ]);

            if (!$user->freelancer_service_created && !in_array($routeName, $serviceAllowed)) {
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
            ];

            if (!$isActivated) {
                return redirect()->route('onboarding.digital-product')
                    ->with('warning', 'Pay the digital product activation fee to start selling.');
            }

            if (!$user->digital_product_uploaded && !in_array($routeName, $digitalAllowed)) {
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
            ];

            if (!$isActivated) {
                return redirect()->route('onboarding.growth')
                    ->with('warning', 'Pay the growth marketplace activation fee to start listing.');
            }

            if (!$user->growth_listing_created && !in_array($routeName, $growthAllowed)) {
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
            return redirect()->route('start-your-journey')
                ->with('warning', 'Unlock ' . str_replace('_', ' ', $featureRequired) . ' to access this section.');
        }

        // =============================================
        // BUYER FEATURE ACCESS CHECKS
        // =============================================
        if ($user->account_type === 'buyer') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            
           
            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasBuyerFeature('professional_services')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }
            
            // Buyers need to unlock task creation
            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasBuyerFeature('task_creation')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }
            
            // Buyers need to unlock available tasks access
            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasBuyerFeature('available_tasks')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
        }

        // =============================================
        // TASK CREATOR FEATURE ACCESS CHECKS
        // =============================================
        if ($user->account_type === 'task_creator') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Task Creators cannot access available tasks by default
            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasTaskCreatorFeature('available_tasks')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
            
            // Task Creators need to unlock professional services
            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasTaskCreatorFeature('professional_services')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }
            
            // Task Creators need to unlock growth listings
            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasTaskCreatorFeature('growth_listings')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }
            
            // Task Creators need to unlock digital products
            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasTaskCreatorFeature('digital_products')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        // =============================================
        // FREELANCER FEATURE ACCESS CHECKS
        // =============================================
        if ($user->account_type === 'freelancer') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Freelancers cannot access task creation by default
            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasFreelancerFeature('task_creation')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }
            
            // Freelancers need to unlock available tasks
            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasFreelancerFeature('available_tasks')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
            
            // Freelancers need to unlock growth listings
            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasFreelancerFeature('growth_listings')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }
            
            // Freelancers need to unlock digital products
            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasFreelancerFeature('digital_products')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        // =============================================
        // DIGITAL SELLER FEATURE ACCESS CHECKS
        // =============================================
        if ($user->account_type === 'digital_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Digital Sellers cannot access task creation
            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasDigitalSellerFeature('task_creation')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }
            
            // Digital Sellers need to unlock available tasks
            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasDigitalSellerFeature('available_tasks')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
            
            // Digital Sellers need to unlock professional services
            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasDigitalSellerFeature('professional_services')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }
            
            // Digital Sellers need to unlock growth listings
            if (str_starts_with($routeName ?? '', 'growth.') && !$user->hasDigitalSellerFeature('growth_listings')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock growth listings to access this feature.');
            }
        }

        // =============================================
        // GROWTH SELLER FEATURE ACCESS CHECKS
        // =============================================
        if ($user->account_type === 'growth_seller') {
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Growth Sellers cannot access task creation
            if (str_starts_with($routeName ?? '', 'tasks.create') && !$user->hasGrowthSellerFeature('task_creation')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock task creation to access this feature.');
            }
            
            // Growth Sellers need to unlock available tasks
            if (($routeName === 'tasks.index' || str_starts_with($routeName ?? '', 'tasks.available')) && !$user->hasGrowthSellerFeature('available_tasks')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock available tasks to browse tasks.');
            }
            
            // Growth Sellers need to unlock professional services
            if (str_starts_with($routeName ?? '', 'professional-services') && !$user->hasGrowthSellerFeature('professional_services')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock professional services to access this feature.');
            }
            
            // Growth Sellers need to unlock digital products
            if (str_starts_with($routeName ?? '', 'digital-products') && !$user->hasGrowthSellerFeature('digital_products')) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Unlock digital products to access this feature.');
            }
        }

        return $next($request);
    }
}