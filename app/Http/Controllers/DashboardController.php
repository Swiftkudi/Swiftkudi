<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\TaskBundle;
use App\Models\Badge;
use App\Models\Referral;
use App\Services\SwiftKudiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Main dashboard
     */
    public function index()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $wallet = $user->wallet;

        // Check activation status
        $isActivated = $wallet && $wallet->is_activated;
        
        // Get activation fee settings
        $activationFeeEnabled = \App\Models\SystemSetting::isCompulsoryActivationFee();
        $activationFee = $activationFeeEnabled ? \App\Models\SystemSetting::getActivationFeeForUser(false) : 0;

        // Get stats based on role
        $stats = $this->getUserStats($user, $wallet);

        // Get recent activities
        $recentTasks = [];
        $recentCompletions = [];

        if ($isActivated) {
            if ($wallet->withdrawable_balance > 0 || $wallet->promo_credit_balance > 0) {
                // Worker stats
                $recentCompletions = TaskCompletion::where('user_id', $user->id)
                    ->with('task')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            }

            if ($user->tasks()->exists()) {
                // Client stats
                $recentTasks = Task::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            }
        }

        // Gamification data
        $levelProgress = $user->getXpProgress();
        $badges = $user->badges()->with('badge')->get();
        $earnedBadgeIds = $user->badges->pluck('badge_id')->toArray();
        $availableBadges = Badge::whereNotIn('id', $earnedBadgeIds)->get();

        // Referral info
        $referralCode = $user->referral_code;
        if (empty($referralCode)) {
            $referralCode = User::generateReferralCode($user->name ?? $user->email ?? (string) $user->id);
            $user->referral_code = $referralCode;
            $user->save();
        }
        $referrals = Referral::where('user_id', $user->id)->count();
        $activatedReferrals = Referral::where('user_id', $user->id)
            ->where('is_activated', true)
            ->count();

        // Ensure permanent referral task exists and always show it at the top
        $referralTask = SwiftKudiService::ensurePermanentReferralTask();
        
        // Available tasks for workers - always show referral task at the top
        $availableTasks = [];
        
        // Get regular available tasks
        $regularTasksQuery = Task::active()
            ->where('user_id', '!=', $user->id)
            ->where('is_permanent_referral', false);
        
        if ($wallet && $isActivated) {
            $hasLowBalance = $user->wallet->withdrawable_balance + $user->wallet->promo_credit_balance <= 0;
            if ($hasLowBalance) {
                // User needs to earn, show more available tasks
                $regularTasks = $regularTasksQuery->whereDoesntHave('completions', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'approved']);
                })->take(4)->get();
            } else {
                // User has balance, still show referral task but fewer regular tasks
                $regularTasks = $regularTasksQuery->whereDoesntHave('completions', function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->whereIn('status', ['pending', 'approved']);
                })->take(2)->get();
            }
        } else {
            $regularTasks = collect();
        }
        
        // Combine referral task with regular tasks (referral task first)
        $availableTasks = $referralTask 
            ? collect([$referralTask])->concat($regularTasks)
            : $regularTasks;

        // Featured bundles
        $featuredBundles = TaskBundle::where('is_active', true)
            ->whereNull('expires_at')
            ->orWhere('expires_at', '>', now())
            ->take(3)
            ->get();

        // Recent tasks (created by user)
        $myTasks = $user->tasks()->withCount(['completions as completions_count' => function ($query) {
            $query->where('status', TaskCompletion::STATUS_APPROVED);
        }])->get();

        return view('dashboard', compact(
            'user',
            'wallet',
            'isActivated',
            'stats',
            'recentTasks',
            'recentCompletions',
            'levelProgress',
            'badges',
            'availableBadges',
            'referralCode',
            'referrals',
            'activatedReferrals',
            'availableTasks',
            'referralTask',
            'featuredBundles',
            'myTasks',
            'activationFeeEnabled',
            'activationFee'
        ));
    }

    /**
     * Get user statistics based on role
     */
    protected function getUserStats(User $user, ?Wallet $wallet): array
    {
        $stats = [
            'role' => 'worker',
            'tasks_completed' => 0,
            'tasks_created' => 0,
            'total_earned' => 0,
            'total_spent' => 0,
            'pending_earnings' => 0,
            'level' => $user->level,
            'xp' => $user->experience_points,
            'streak' => $user->daily_streak,
            'total_referrals' => 0,
            'referral_earnings' => 0,
        ];

        if (!$wallet) {
            return $stats;
        }

        // Worker stats
        $stats['total_earned'] = $wallet->total_earned;
        $stats['total_spent'] = $wallet->total_spent;
        $stats['tasks_completed'] = TaskCompletion::where('user_id', $user->id)
            ->approved()
            ->count();
        
        // Pending earnings
        $stats['pending_earnings'] = TaskCompletion::where('user_id', $user->id)
            ->pending()
            ->count();

        // Client stats
        $stats['tasks_created'] = Task::where('user_id', $user->id)->count();

        // Referral stats - use actual sum of reward_earned like ReferralController
        $stats['total_referrals'] = Referral::where('user_id', $user->id)->count();
        $stats['referral_earnings'] = Referral::where('user_id', $user->id)->sum('reward_earned');

        // Determine primary role
        if ($stats['tasks_created'] > 0 && $stats['tasks_completed'] > 0) {
            $stats['role'] = 'both';
        } elseif ($stats['tasks_created'] > 0) {
            $stats['role'] = 'client';
        }

        return $stats;
    }

    /**
     * Worker-specific dashboard
     */
    public function worker()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $wallet = $user->wallet ?? Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'withdrawable_balance' => 0,
                'promo_credit_balance' => 0,
                'total_earned' => 0,
                'total_spent' => 0,
                'pending_balance' => 0,
                'escrow_balance' => 0,
            ]
        );

        if (!$wallet->is_activated && !session()->has('activation_skip_notice_dismissed')) {
            session()->flash('info', 'Activation is optional for now. You can continue and activate later when you want to unlock withdrawals.');
        }

        // Ensure permanent referral task exists
        $referralTask = SwiftKudiService::ensurePermanentReferralTask();

        // Available tasks
        $availableTasks = Task::active()
            ->where('user_id', '!=', $user->id)
            ->where('is_permanent_referral', false)
            ->whereDoesntHave('completions', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'approved']);
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('worker_reward_per_task', 'desc')
            ->paginate(20);

        // My submissions
        $mySubmissions = TaskCompletion::where('user_id', $user->id)
            ->with('task')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats
        $stats = [
            'completed' => TaskCompletion::where('user_id', $user->id)->approved()->count(),
            'pending' => TaskCompletion::where('user_id', $user->id)->pending()->count(),
            'rejected' => TaskCompletion::where('user_id', $user->id)->where('status', 'rejected')->count(),
            'total_earned' => $wallet->total_earned,
        ];

        return view('dashboard.worker', compact(
            'availableTasks',
            'mySubmissions',
            'stats',
            'referralTask'
        ));
    }

    /**
     * Client-specific dashboard
     */
    public function client()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $wallet = $user->wallet;

        // My campaigns (tasks)
        $campaigns = Task::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Campaign stats
        $stats = [
            'total_campaigns' => Task::where('user_id', $user->id)->count(),
            'active_campaigns' => Task::where('user_id', $user->id)->active()->count(),
            'total_submissions' => TaskCompletion::whereHas('task', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'total_spent' => $wallet ? $wallet->total_spent : 0,
        ];

        // Pending approvals
        $pendingApprovals = TaskCompletion::whereHas('task', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pending()->count();

        return view('dashboard.client', compact(
            'campaigns',
            'stats',
            'pendingApprovals'
        ));
    }

    /**
     * Leaderboard
     */
    public function leaderboard(Request $request)
    {
        $type = $request->get('type', 'earners');

        if ($type === 'earners') {
            $leaders = Wallet::where('is_activated', true)
                ->orderBy('total_earned', 'desc')
                ->with('user')
                ->take(50)
                ->get();
        } elseif ($type === 'tasks') {
            $leaders = User::withCount(['taskCompletions' => function ($query) {
                $query->approved();
            }])
            ->where('level', '>=', 1)
            ->orderBy('task_completions_count', 'desc')
            ->take(50)
            ->get();
        } elseif ($type === 'streaks') {
            $leaders = User::where('daily_streak', '>', 0)
                ->orderBy('daily_streak', 'desc')
                ->take(50)
                ->get();
        } else {
            $leaders = User::where('experience_points', '>', 0)
                ->orderBy('experience_points', 'desc')
                ->take(50)
                ->get();
        }

        $currentUser = Auth::user();
        /** @var \App\Models\User $currentUser */
        $currentUserRank = null;

        if ($type === 'earners') {
            $currentUserRank = Wallet::where('is_activated', true)
                ->where('total_earned', '>', $currentUser->wallet->total_earned ?? 0)
                ->count() + 1;
        } elseif ($type === 'tasks') {
            $userTaskCount = TaskCompletion::where('user_id', $currentUser->id)->approved()->count();
            $currentUserRank = User::whereHas('taskCompletions', function ($query) {
                $query->approved();
            })
            ->withCount(['taskCompletions' => function ($query) {
                $query->approved();
            }])
            ->get()
            ->filter(function ($u) use ($userTaskCount) {
                return $u->task_completions_count > $userTaskCount;
            })
            ->count() + 1;
        }

        return view('dashboard.leaderboard', compact(
            'leaders',
            'type',
            'currentUserRank'
        ));
    }

    /**
     * Profile page
     */
    public function profile()
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */
        $wallet = $user->wallet;
        $stats = [
            'tasks_completed' => TaskCompletion::where('user_id', $user->id)->approved()->count(),
            'tasks_created' => Task::where('user_id', $user->id)->count(),
            'total_earned' => $wallet ? $wallet->total_earned : 0,
            'level' => $user->level,
            'xp' => $user->experience_points,
            'streak' => $user->daily_streak,
        ];

        return view('dashboard.profile', compact('user', 'wallet', 'stats'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $user */

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->back()
            ->with('success', 'Profile updated successfully.');
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return redirect()->route('login');
            }

            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', 'Your account has been deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('User failed to delete own account', [
                'user_id' => optional($request->user())->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Unable to delete your account right now.');
        }
    }
}
