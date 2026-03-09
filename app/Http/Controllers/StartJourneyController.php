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
            'recentPayouts'
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
