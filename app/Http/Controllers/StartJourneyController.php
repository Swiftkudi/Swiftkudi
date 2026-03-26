<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\TaskBundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StartJourneyController extends Controller
{
    /**
     * Get pre-built bundle templates for the journey page.
     * These are templates that users can click to auto-fill the task creation form.
     */
    protected function getBundleTemplates(): array
    {
        return [
            [
                'id' => 'social_growth',
                'name' => 'Social Growth Bundle',
                'description' => 'Perfect for growing your social media presence quickly',
                'total_price' => 2500,
                'worker_reward' => 2000,
                'total_tasks' => 5,
                'difficulty_level' => 'easy',
                'tasks' => [
                    ['title' => 'Instagram Like Campaign', 'reward' => 200, 'platform' => 'Instagram', 'quantity' => 5],
                    ['title' => 'Twitter Follow Campaign', 'reward' => 300, 'platform' => 'Twitter', 'quantity' => 5],
                    ['title' => 'Facebook Like Campaign', 'reward' => 200, 'platform' => 'Facebook', 'quantity' => 5],
                    ['title' => 'TikTok Share Campaign', 'reward' => 250, 'platform' => 'TikTok', 'quantity' => 5],
                    ['title' => 'YouTube Subscribe Campaign', 'reward' => 350, 'platform' => 'YouTube', 'quantity' => 3],
                ],
            ],
            [
                'id' => 'brand_awareness',
                'name' => 'Brand Awareness Bundle',
                'description' => 'Maximum reach for your brand or product',
                'total_price' => 5000,
                'worker_reward' => 4000,
                'total_tasks' => 10,
                'difficulty_level' => 'medium',
                'tasks' => [
                    ['title' => 'Instagram Story Engagement', 'reward' => 250, 'platform' => 'Instagram', 'quantity' => 10],
                    ['title' => 'Twitter Retweet Campaign', 'reward' => 300, 'platform' => 'Twitter', 'quantity' => 10],
                    ['title' => 'Facebook Post Share', 'reward' => 300, 'platform' => 'Facebook', 'quantity' => 10],
                    ['title' => 'LinkedIn Post Engagement', 'reward' => 400, 'platform' => 'LinkedIn', 'quantity' => 5],
                    ['title' => 'TikTok Video Views', 'reward' => 250, 'platform' => 'TikTok', 'quantity' => 20],
                ],
            ],
            [
                'id' => 'ugc_starter',
                'name' => 'UGC Starter Pack',
                'description' => 'User-generated content for authentic engagement',
                'total_price' => 7500,
                'worker_reward' => 6000,
                'total_tasks' => 8,
                'difficulty_level' => 'hard',
                'tasks' => [
                    ['title' => 'Instagram Reel Creation', 'reward' => 1000, 'platform' => 'Instagram', 'quantity' => 3],
                    ['title' => 'TikTok Product Video', 'reward' => 1000, 'platform' => 'TikTok', 'quantity' => 3],
                    ['title' => 'YouTube Short Review', 'reward' => 1500, 'platform' => 'YouTube', 'quantity' => 2],
                    ['title' => 'Twitter Thread Creation', 'reward' => 750, 'platform' => 'Twitter', 'quantity' => 4],
                    ['title' => 'Instagram Photo Post', 'reward' => 750, 'platform' => 'Instagram', 'quantity' => 4],
                ],
            ],
        ];
    }

    /**
     * Display the start-your-journey page.
     */
    public function index()
    {
        $user = Auth::user();

        // If admin disabled mandatory task-creation gate, user is already unlocked.
        if (!SystemSetting::isMandatoryTaskCreationEnabled()) {
            return redirect()->route('dashboard')
                ->with('info', 'Task-creation requirement is currently disabled. You can start earning immediately.');
        }

        // Get settings
        $minimumBudget = SystemSetting::get('minimum_required_budget', 1000);
        $currency = SystemSetting::get('mandatory_budget_currency', 'NGN');

        // Get user's current progress
        $userBudget = $user->total_created_task_budget ?? 0;
        $progress = min(100, ($userBudget / $minimumBudget) * 100);
        $remaining = max(0, $minimumBudget - $userBudget);

        // Get bundle templates (hardcoded, not from database)
        $bundles = $this->getBundleTemplates();

        // Get available tasks for preview (read-only) - use scopeActive()
        $availableTasks = Task::active()
            ->where('user_id', '!=', $user->id)
            ->orderBy('worker_reward_per_task', 'desc')
            ->limit(6)
            ->get();

        // Earner-specific flags and tasks
        $isEarner = $user->account_type === 'earner';
        $activationFee = SystemSetting::get('activation_fee', 1500);
        $activationPaid = $user->activation_paid;
        $referralTaskCompleted = $user->referral_task_completed ?? false;
        $referralTaskSkipped = $user->referral_task_skipped ?? false;

        $accountType = $user->account_type;

        // Build role-specific features based on account type
        $roleFeatures = [];
        $featureRoute = '';

        switch ($accountType) {
            case 'buyer':
                $roleFeatures = [
                    'start_journey' => [
                        'label' => 'Start Journey Access',
                        'unlocked' => $user->hasBuyerFeature('start_journey'),
                        'expires' => $user->getBuyerFeatureExpiry('start_journey'),
                    ],
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasBuyerFeature('task_creation'),
                        'expires' => $user->getBuyerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasBuyerFeature('available_tasks'),
                        'expires' => $user->getBuyerFeatureExpiry('available_tasks'),
                    ],
                ];
                $featureRoute = 'onboarding.buyer.feature.unlock';
                break;

            case 'earner':
                $roleFeatures = [
                    'freelance_marketplace' => [
                        'label' => 'Freelance Marketplace',
                        'unlocked' => $user->hasEarnerFeature('freelance_marketplace'),
                        'expires' => $user->getEarnerFeatureExpiry('freelance_marketplace'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasEarnerFeature('digital_products'),
                        'expires' => $user->getEarnerFeatureExpiry('digital_products'),
                    ],
                    'growth_marketplace' => [
                        'label' => 'Growth Marketplace',
                        'unlocked' => $user->hasEarnerFeature('growth_marketplace'),
                        'expires' => $user->getEarnerFeatureExpiry('growth_marketplace'),
                    ],
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasEarnerFeature('task_creation'),
                        'expires' => $user->getEarnerFeatureExpiry('task_creation'),
                    ],
                ];
                $featureRoute = 'onboarding.earn.feature.unlock';
                break;

            case 'task_creator':
                $roleFeatures = [
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasTaskCreatorFeature('available_tasks'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasTaskCreatorFeature('professional_services'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('professional_services'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasTaskCreatorFeature('growth_listings'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('growth_listings'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasTaskCreatorFeature('digital_products'),
                        'expires' => $user->getTaskCreatorFeatureExpiry('digital_products'),
                    ],
                ];
                $featureRoute = 'onboarding.task-creator.feature.unlock';
                break;

            case 'freelancer':
                $roleFeatures = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasFreelancerFeature('task_creation'),
                        'expires' => $user->getFreelancerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasFreelancerFeature('available_tasks'),
                        'expires' => $user->getFreelancerFeatureExpiry('available_tasks'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasFreelancerFeature('growth_listings'),
                        'expires' => $user->getFreelancerFeatureExpiry('growth_listings'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasFreelancerFeature('digital_products'),
                        'expires' => $user->getFreelancerFeatureExpiry('digital_products'),
                    ],
                ];
                $featureRoute = 'onboarding.freelancer.feature.unlock';
                break;

            case 'digital_seller':
                $roleFeatures = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasDigitalSellerFeature('task_creation'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasDigitalSellerFeature('available_tasks'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasDigitalSellerFeature('professional_services'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('professional_services'),
                    ],
                    'growth_listings' => [
                        'label' => 'Growth Listings',
                        'unlocked' => $user->hasDigitalSellerFeature('growth_listings'),
                        'expires' => $user->getDigitalSellerFeatureExpiry('growth_listings'),
                    ],
                ];
                $featureRoute = 'onboarding.digital-seller.feature.unlock';
                break;

            case 'growth_seller':
                $roleFeatures = [
                    'task_creation' => [
                        'label' => 'Task Creation',
                        'unlocked' => $user->hasGrowthSellerFeature('task_creation'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('task_creation'),
                    ],
                    'available_tasks' => [
                        'label' => 'Available Tasks',
                        'unlocked' => $user->hasGrowthSellerFeature('available_tasks'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('available_tasks'),
                    ],
                    'professional_services' => [
                        'label' => 'Professional Services',
                        'unlocked' => $user->hasGrowthSellerFeature('professional_services'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('professional_services'),
                    ],
                    'digital_products' => [
                        'label' => 'Digital Products',
                        'unlocked' => $user->hasGrowthSellerFeature('digital_products'),
                        'expires' => $user->getGrowthSellerFeatureExpiry('digital_products'),
                    ],
                ];
                $featureRoute = 'onboarding.growth-seller.feature.unlock';
                break;

            default:
                $roleFeatures = [];
                $featureRoute = '';
        }

        // Keep for backwards compatibility
        $earnerFeatures = $roleFeatures;

        $referralOnboardingTask = [
            'title' => 'Referral Task',
            'reward' => 500,
            'description' => 'Invite a friend and earn₦500 as a first onboarding exercise.',
        ];

        // Micro and UGC tasks preview for activated earners
        $microTasks = Task::active()->whereIn('task_type', ['like', 'follow', 'comment', 'share', 'view', 'retweet'])->limit(4)->get();
        $ugcTasks = Task::active()->whereIn('task_type', ['testimonial', 'review', 'promo_video', 'story'])->limit(4)->get();

        // Calculate potential earnings preview
        $potentialEarnings = $availableTasks->sum('worker_reward_per_task');
        $avgEarningsPerBundle = $potentialEarnings / max(1, count($availableTasks));

        // Platform stats for motivation
        $activeWorkers = SystemSetting::get('active_workers_count', 1250);
        $totalPaidOut = SystemSetting::get('total_paid_out', 4500000);
        $recentPayouts = [
            ['amount' => 1500, 'time' => '2 hours ago'],
            ['amount' => 2800, 'time' => '5 hours ago'],
            ['amount' => 750, 'time' => '6 hours ago'],
            ['amount' => 3200, 'time' => '8 hours ago'],
        ];

        return view('start-journey.index', compact(
            'user',
            'minimumBudget',
            'currency',
            'userBudget',
            'progress',
            'remaining',
            'bundles',
            'availableTasks',
            'potentialEarnings',
            'avgEarningsPerBundle',
            'activeWorkers',
            'totalPaidOut',
            'recentPayouts',
            'isEarner',
            'activationFee',
            'activationPaid',
            'referralTaskCompleted',
            'referralTaskSkipped',
            'earnerFeatures',
            'referralOnboardingTask',
            'microTasks',
            'ugcTasks',
            'roleFeatures',
            'featureRoute',
            'accountType'
        ));
    }

    /**
     * Apply a bundle template to pre-fill the task creation form.
     */
    public function applyBundle(Request $request)
    {
        $user = Auth::user();

        // Get bundle ID from request
        $bundleId = $request->input('bundle_id');

        // Find the bundle template
        $bundles = $this->getBundleTemplates();
        $bundle = null;
        foreach ($bundles as $b) {
            if ($b['id'] === $bundleId) {
                $bundle = $b;
                break;
            }
        }

        if (!$bundle) {
            return redirect()->route('start-your-journey')
                ->with('error', 'Bundle not found.');
        }

        // Check if user is still gated
        if (!$this->canCreateTasks($user)) {
            return redirect()->route('start-your-journey')
                ->with('error', 'Please complete the mandatory task creation requirement.');
        }

        // Store bundle data in session for task creation
        session(['bundle_data' => $bundle]);

        return redirect()->route('tasks.create')
            ->with('info', "{$bundle['name']} loaded! Customize and publish your campaign.");
    }

    /**
     * Check if user can create tasks.
     */
    private function canCreateTasks($user): bool
    {
        // Admins can always create tasks
        if ($user->is_admin) {
            return true;
        }

        // Check if gate is enabled
        $gateEnabled = SystemSetting::get('mandatory_task_creation_enabled', true);

        if (!$gateEnabled) {
            return true;
        }

        // Users who completed the requirement can create tasks
        if ($user->has_completed_mandatory_creation) {
            return true;
        }

        // Everyone can create tasks, just can't earn until threshold is met
        return true;
    }

    /**
     * Check unlock status via AJAX.
     */
    public function checkUnlockStatus()
    {
        $user = Auth::user();

        if (!SystemSetting::isMandatoryTaskCreationEnabled()) {
            return response()->json([
                'unlocked' => true,
                'current_budget' => $user->total_created_task_budget ?? 0,
                'required_budget' => 0,
                'remaining' => 0,
            ]);
        }

        $minimumBudget = SystemSetting::get('minimum_required_budget', 2500);
        $userBudget = $user->total_created_task_budget ?? 0;

        $isUnlocked = $userBudget >= $minimumBudget;

        return response()->json([
            'unlocked' => $isUnlocked,
            'current_budget' => $userBudget,
            'required_budget' => $minimumBudget,
            'remaining' => max(0, $minimumBudget - $userBudget),
        ]);
    }

    /**
     * Handle the unlock success - show modal and send notification.
     */
    public function unlockSuccess()
    {
        $user = Auth::user();

        // Check if this is actually the first unlock
        if (!$user->task_creation_unlocked_at) {
            return redirect()->route('dashboard');
        }

        return view('start-journey.unlock-success');
    }
}
