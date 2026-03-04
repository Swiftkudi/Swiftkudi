<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletLedger;
use App\Models\Referral;
use App\Models\FraudLog;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\TaskBundle;
use App\Models\TaskCategory;
use App\Models\RevenueReport;
use App\Models\SystemSetting;
use App\Models\ActivationLog;
use App\Services\RevenueAggregator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SwiftKudiService
{
    /**
     * Get the default currency code from settings
     */
    public static function getCurrencyCode(): string
    {
        return SystemSetting::get('default_currency', 'NGN');
    }

    /**
     * Get the currency symbol for display
     */
    public static function getCurrencySymbol(): string
    {
        $code = self::getCurrencyCode();
        switch ($code) {
            case 'USD':
                return '$';
            case 'USDT':
                return '₮';
            case 'NGN':
            default:
                return '₦';
        }
    }

    /**
     * Format amount with currency symbol
     */
    public static function formatCurrency(float $amount): string
    {
        return self::getCurrencySymbol() . number_format($amount, 2);
    }

    /**
     * Activation fee constants
     */
    public const ACTIVATION_FEE = 1000;
    public const REFERRED_ACTIVATION_FEE = 2000;
    public const REFERRER_BONUS = 1000;
    public const PLATFORM_REVENUE = 1000;

    /**
     * Withdrawal constants
     */
    public const MIN_WITHDRAWAL = 3000;
    public const STANDARD_FEE_PERCENT = 5;
    public const INSTANT_FEE_PERCENT = 10;

    /**
     * Earnings split
     */
    public const WITHDRAWABLE_RATIO = 0.80;
    public const PROMO_CREDIT_RATIO = 0.20;

    /**
     * Task budget constants
     */
    public const MIN_TASK_BUNDLE_BUDGET = 2500;
    public const MIN_MACRO_TASK_BUDGET = 1000;
    public const PLATFORM_COMMISSION = 25;

    /**
     * Task type groups
     */
    public const TYPE_GROUP_MICRO = 'micro';
    public const TYPE_GROUP_UGC = 'ugc';
    public const TYPE_GROUP_REFERRAL = 'referral';
    public const TYPE_GROUP_PREMIUM = 'premium';

    /**
     * Record a transaction in the ledger
     *
     * @param User $user
     * @param string $type
     * @param float $amount
     * @param int|null $walletId
     * @param string|null $description
     * @return Transaction
     */
    public function recordTransaction(
        User $user,
        string $type,
        float $amount,
        ?int $walletId = null,
        ?string $description = null
    ): Transaction {
        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $walletId,
            'type' => $type,
            'amount' => $amount,
            'status' => 'completed',
            'description' => $description,
            'reference' => 'TXN-' . strtoupper(uniqid()),
        ]);
    }

    /**
     * Activate a user's account
     */
    public function activateUser(User $user, ?User $referrer = null): array
    {
        return DB::transaction(function () use ($user, $referrer) {
            // Get or create wallet with error handling for missing columns
            try {
                $wallet = $user->wallet ?? Wallet::create([
                    'user_id' => $user->id,
                    'withdrawable_balance' => 0,
                    'promo_credit_balance' => 0,
                    'total_earned' => 0,
                    'total_spent' => 0,
                    'pending_balance' => 0,
                    'escrow_balance' => 0,
                ]);
            } catch (\Exception $e) {
                Log::warning('Wallet creation failed, trying without earning categories', ['error' => $e->getMessage()]);
                $wallet = Wallet::firstOrCreate(
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
            }

            // Check if already activated
            if ($wallet->is_activated) {
                return [
                    'success' => false,
                    'message' => 'Account is already activated',
                ];
            }

            // Prevent self-referral
            if ($referrer && $referrer->id === $user->id) {
                $referrer = null; // Ignore self-referral
                Log::warning('Self-referral detected and ignored', ['user_id' => $user->id]);
            }

            // Determine activation fee using SystemSetting (supports referred users discount)
            $isReferred = $referrer !== null;
            $activationFeeRequired = SystemSetting::isCompulsoryActivationFee();
            $activationFee = $activationFeeRequired
                ? SystemSetting::getActivationFeeForUser($isReferred)
                : 0;
            $platformRevenue = $activationFee;

            if ($activationFee > 0) {
                // Check if user has enough balance
                $totalBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
                if ($totalBalance < $activationFee) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient balance. You need ' . self::formatCurrency($activationFee) . ' to activate. Please deposit funds first.',
                        'needs_deposit' => true,
                    ];
                }

                // Deduct activation fee: use promo credit first, then withdrawable
                $remaining = $activationFee;
                if ($wallet->promo_credit_balance > 0) {
                    $usePromo = min($wallet->promo_credit_balance, $remaining);
                    $wallet->deductPromoCredit($usePromo, 'activation');
                    $remaining -= $usePromo;
                }
                if ($remaining > 0) {
                    $ok = $wallet->deductWithdrawable($remaining, 'activation');
                    if (!$ok) {
                        return [
                            'success' => false,
                            'message' => 'Insufficient withdrawable balance to complete activation. Please deposit funds.',
                            'needs_deposit' => true,
                        ];
                    }
                }
            }

            // Mark wallet as activated
            $wallet->is_activated = true;
            $wallet->activated_at = now();
            $wallet->save();

            // Create activation log (wrap in try-catch in case table doesn't exist)
            $referralBonusAmount = $isReferred ? SystemSetting::getReferralBonusAmount() : 0;
            $platformRevenue = max(0, $activationFee - $referralBonusAmount);
            
            try {
                ActivationLog::create([
                    'user_id' => $user->id,
                    'activation_type' => $isReferred ? 'referral' : 'normal',
                    'activation_fee' => $activationFee,
                    'referral_bonus' => $referralBonusAmount,
                    'platform_revenue' => $platformRevenue,
                    'status' => 'completed',
                    'reference' => 'ACT-' . strtoupper(uniqid()),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to create activation log', ['error' => $e->getMessage()]);
            }

            // Auto-aggregate revenue for today (wrap in try-catch)
            try {
                RevenueAggregator::aggregateForDate(now()->toDateString());
            } catch (\Exception $e) {
                Log::warning('Failed to aggregate revenue', ['error' => $e->getMessage()]);
            }

            // Credit referrer if applicable
            if ($isReferred && $referrer) {
                $referrerBonus = SystemSetting::getReferralBonusAmount();
                if ($referrer->wallet) {
                    try {
                        $referrer->wallet->addWithdrawable($referrerBonus, 'referral_bonus', 'Referral bonus for activating ' . $user->name);
                    } catch (\Exception $e) {
                        Log::warning('Failed to credit referrer bonus', ['error' => $e->getMessage()]);
                    }
                }

                // Create referral record if not exists
                try {
                    $referral = Referral::firstOrNew([
                        'user_id' => $referrer->id,
                        'referred_user_id' => $user->id,
                    ]);

                    if (!$referral->exists) {
                        $referral->referral_code = $referrer->referral_code ?: Referral::generateReferralCode($referrer->id);
                        $referral->referred_email = $user->email;
                    }

                    $referral->is_registered = true;
                    $referral->is_activated = true;
                    $referral->reward_earned = ($referral->reward_earned ?? 0) + $referrerBonus;
                    $referral->save();
                } catch (\Exception $e) {
                    Log::warning('Failed to update referral record', ['error' => $e->getMessage()]);
                }
            }

            return [
                'success' => true,
                'message' => 'Account activated successfully!',
                'data' => [
                    'wallet' => $wallet,
                    'activation_fee' => $activationFee,
                    'referral_bonus' => $isReferred ? SystemSetting::getReferralBonusAmount() : 0,
                ],
            ];
        });
    }

    /**
     * Ensure the permanent referral bonus task exists and is active
     * This task is visible to all users and cannot be completed in the traditional sense
     * It displays referral bonus earnings opportunity
     */
    public static function ensurePermanentReferralTask(): ?Task
    {
        // Check if feature is enabled
        if (!SystemSetting::isReferralBonusTaskEnabled()) {
            return null;
        }

        // Find existing permanent referral task
        $existingTask = Task::where('is_permanent_referral', true)->first();

        if ($existingTask) {
            // Update if exists
            $bonusAmount = SystemSetting::getReferralBonusAmount();
            $targetReferrals = SystemSetting::getNumber('referral_bonus_target', 20);
            $totalBonus = $bonusAmount * $targetReferrals;
            $currency = self::getCurrencySymbol();
            $existingTask->update([
                'worker_reward_per_task' => $bonusAmount,
                'title' => 'Referral Bonus - Earn ' . $currency . number_format($totalBonus) . ' for ' . $targetReferrals . ' Referrals!',
                'description' => 'Invite friends to join SwiftKudi and earn ' . $currency . number_format($bonusAmount) . ' for each friend who activates their account. Complete ' . $targetReferrals . ' activated referrals to earn ' . $currency . number_format($totalBonus) . ' bonus! Share your referral code and start earning today! This is a permanent task that stays active indefinitely.',
                'is_active' => true,
            ]);
            return $existingTask;
        }

        // Create new permanent referral task
        $bonusAmount = SystemSetting::getReferralBonusAmount();
        $targetReferrals = SystemSetting::getNumber('referral_bonus_target', 20);
        $totalBonus = $bonusAmount * $targetReferrals;
        $currency = self::getCurrencySymbol();
        
        // Find the first available user
        $firstUser = \App\Models\User::first();
        
        return Task::create([
            'user_id' => $firstUser ? $firstUser->id : 1,
            'title' => 'Referral Bonus - Earn ' . $currency . number_format($totalBonus) . ' for ' . $targetReferrals . ' Referrals!',
            'description' => 'Invite friends to join SwiftKudi and earn ' . $currency . number_format($bonusAmount) . ' for each friend who activates their account. Complete ' . $targetReferrals . ' activated referrals to earn ' . $currency . number_format($totalBonus) . ' bonus! Share your referral code and start earning today! This is a permanent task that stays active indefinitely.',
            'platform' => 'other',
            'task_type' => Task::TYPE_REFERRAL,
            'target_url' => url('/register'),
            'proof_instructions' => 'Share your referral code with friends. You earn when they register and activate their account.',
            'proof_type' => Task::PROOF_LINK,
            'budget' => 0,
            'quantity' => 0,
            'worker_reward_per_task' => $bonusAmount,
            'platform_commission' => 0,
            'escrow_amount' => 0,
            'min_level' => 0,
            'is_active' => true,
            'is_approved' => true,
            'is_featured' => false,
            'is_sample' => false,
            'is_permanent_referral' => true,
            'max_submissions_per_user' => 0, // Unlimited
            'starts_at' => now(),
            'expires_at' => null, // Never expires
        ]);
    }

    /**
     * Calculate withdrawal fee
     */
    public static function calculateWithdrawalFee(float $amount, bool $isInstant = false): float
    {
        $percent = $isInstant ? self::INSTANT_FEE_PERCENT : self::STANDARD_FEE_PERCENT;
        return ($amount * $percent) / 100;
    }

    /**
     * Calculate platform commission from task budget
     */
    public static function calculatePlatformCommission(float $budget): float
    {
        return ($budget * self::PLATFORM_COMMISSION) / 100;
    }

    /**
     * Calculate earnings split (withdrawable vs promo credit)
     */
    public static function calculateEarningsSplit(float $totalEarnings): array
    {
        return [
            'withdrawable' => $totalEarnings * self::WITHDRAWABLE_RATIO,
            'promo_credit' => $totalEarnings * self::PROMO_CREDIT_RATIO,
        ];
    }

    /**
     * Validate task budget meets minimum requirements
     */
    public static function validateTaskBudget(float $budget, string $taskType): array
    {
        switch ($taskType) {
            case 'micro':
                $minBudget = self::MIN_TASK_BUNDLE_BUDGET;
                break;
            case 'macro':
                $minBudget = self::MIN_MACRO_TASK_BUDGET;
                break;
            default:
                $minBudget = self::MIN_TASK_BUNDLE_BUDGET;
        }

        return [
            'valid' => $budget >= $minBudget,
            'minimum' => $minBudget,
            'message' => $budget >= $minBudget 
                ? 'Budget is valid' 
                : "Minimum budget for {$taskType} tasks is " . self::formatCurrency($minBudget),
        ];
    }

    /**
     * Get platform statistics for admin dashboard
     */
    public function getPlatformStats(): array
    {
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $activatedUsers = Wallet::where('is_activated', true)->count();
        
        $totalTasks = Task::count();
        $activeTasks = Task::where('is_active', true)->count();
        $pendingTasks = Task::where('is_approved', false)->count();
        
        $totalWalletBalance = Wallet::sum('withdrawable_balance') + Wallet::sum('promo_credit_balance');
        $totalEarnings = Wallet::sum('total_earned');
        $totalPendingWithdrawals = \App\Models\Withdrawal::where('status', 'pending')->sum('amount');
        $totalCompletedWithdrawals = \App\Models\Withdrawal::where('status', 'completed')->sum('amount');
        $totalWithdrawalFees = \App\Models\Withdrawal::where('status', 'completed')->sum('fee') ?? 0;
        
        $totalTaskCompletions = TaskCompletion::count();
        $pendingCompletions = TaskCompletion::where('status', 'pending')->count();
        $approvedCompletions = TaskCompletion::where('status', 'approved')->count();
        $rejectedCompletions = TaskCompletion::where('status', 'rejected')->count();
        
        $unresolvedFraudLogs = \App\Models\FraudLog::where('is_resolved', false)->count() ?? 0;

        return [
            'total_users' => $totalUsers,
            'new_users_today' => $newUsersToday,
            'activated_users' => $activatedUsers,
            'total_tasks' => $totalTasks,
            'active_tasks' => $activeTasks,
            'pending_tasks' => $pendingTasks,
            'total_wallet_balance' => $totalWalletBalance,
            'total_earnings' => $totalEarnings,
            'total_withdrawals' => $totalCompletedWithdrawals,
            'total_pending_withdrawals' => $totalPendingWithdrawals ?? 0,
            'pending_withdrawals' => \App\Models\Withdrawal::where('status', 'pending')->count() ?? 0,
            'total_fees' => $totalWithdrawalFees,
            'total_task_completions' => $totalTaskCompletions,
            'pending_completions' => $pendingCompletions,
            'approved_completions' => $approvedCompletions,
            'rejected_completions' => $rejectedCompletions,
            'unresolved_fraud_logs' => $unresolvedFraudLogs,
        ];
    }

    /**
     * Award task earnings to worker when completion is approved
     *
     * @param TaskCompletion $completion
     * @return array
     */
    public function awardTaskEarnings(TaskCompletion $completion): array
    {
        return DB::transaction(function () use ($completion) {
            // Check if already processed
            if ($completion->status !== TaskCompletion::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => 'Completion has already been processed',
                ];
            }

            // Check if compulsory task creation is required before earning
            if (SystemSetting::isCompulsoryTaskCreationEnabled()) {
                $taskCount = Task::where('user_id', $completion->user_id)->count();
                if ($taskCount === 0) {
                    Log::warning('Attempted to award earnings without creating a task', [
                        'completion_id' => $completion->id,
                        'user_id' => $completion->user_id,
                    ]);
                    return [
                        'success' => false,
                        'message' => 'You must create at least one task before you can start earning',
                        'requires_task_creation' => true,
                    ];
                }
            }

            // Get the worker's wallet
            $wallet = $completion->user->wallet;
            if (!$wallet) {
                Log::warning('Cannot award task earnings: worker has no wallet', [
                    'completion_id' => $completion->id,
                    'user_id' => $completion->user_id,
                ]);
                return [
                    'success' => false,
                    'message' => 'Worker has no wallet',
                ];
            }

            // Resolve reward amount from completion first, then known task reward columns (legacy + new)
            $task = $completion->task;
            $rewardAmount = (float) ($completion->reward_amount ?? 0);

            if ($rewardAmount <= 0) {
                $rewardAmount = (float) ($task->worker_reward_per_task ?? 0);
            }

            if ($rewardAmount <= 0 && Schema::hasColumn('tasks', 'reward_amount')) {
                $rewardAmount = (float) ($task->reward_amount ?? 0);
            }

            if ($rewardAmount <= 0 && Schema::hasColumn('tasks', 'reward_per_user')) {
                $rewardAmount = (float) ($task->reward_per_user ?? 0);
            }

            if ($rewardAmount <= 0) {
                return [
                    'success' => false,
                    'message' => 'No reward amount specified',
                ];
            }

            if (Schema::hasColumn($completion->getTable(), 'reward_amount')) {
                $completion->reward_amount = $rewardAmount;
            }

            // Calculate split (80% withdrawable, 20% promo credit)
            $split = self::calculateEarningsSplit($rewardAmount);
            $withdrawableAmount = $split['withdrawable'];
            $promoCreditAmount = $split['promo_credit'];

            // Add to wallet
            if ($withdrawableAmount > 0) {
                $wallet->addWithdrawable($withdrawableAmount, 'task_earning', 'Task completion: ' . ($completion->task->title ?? 'Task #' . $completion->task_id));
            }
            if ($promoCreditAmount > 0) {
                $wallet->addPromoCredit($promoCreditAmount, 'task_earning', 'Task completion bonus: ' . ($completion->task->title ?? 'Task #' . $completion->task_id));
            }

            // Record transaction
            $this->recordTransaction(
                $completion->user,
                Transaction::TYPE_TASK_EARNING,
                $rewardAmount,
                $wallet->id,
                'Task completion: ' . ($completion->task->title ?? 'Task #' . $completion->task_id)
            );

            // Update completion status
            $completion->status = TaskCompletion::STATUS_APPROVED;
            $completion->reviewed_at = now();
            $completion->save();

            if ($task) {
                $task->increment('completed_count');
            }

            Log::info('Task earnings awarded', [
                'completion_id' => $completion->id,
                'user_id' => $completion->user_id,
                'amount' => $rewardAmount,
                'withdrawable' => $withdrawableAmount,
                'promo_credit' => $promoCreditAmount,
            ]);

            // Auto-aggregate revenue for today
            RevenueAggregator::aggregateForDate(now()->toDateString());

            return [
                'success' => true,
                'message' => 'Task earnings awarded successfully',
                'data' => [
                    'reward_amount' => $rewardAmount,
                    'withdrawable' => $withdrawableAmount,
                    'promo_credit' => $promoCreditAmount,
                ],
            ];
        });
    }

    /**
     * Check if compulsory task creation before earning is enabled
     */
    public static function isCompulsoryTaskCreationEnabled(): bool
    {
        return SystemSetting::getBool('compulsory_task_creation_before_earning', false);
    }

    /**
     * Check if a user can earn (has created required tasks if setting is enabled)
     *
     * @param User $user
     * @return array
     */
    public static function canUserEarn(User $user): array
    {
        // If compulsory task creation is disabled, users can earn freely
        if (!SystemSetting::isCompulsoryTaskCreationEnabled()) {
            return [
                'can_earn' => true,
                'reason' => null,
            ];
        }

        // Check if user has created at least one task
        $taskCount = Task::where('user_id', $user->id)->count();
        
        if ($taskCount === 0) {
            return [
                'can_earn' => false,
                'reason' => 'compulsory_task_required',
                'message' => 'You must create at least one task before you can start earning',
            ];
        }

        return [
            'can_earn' => true,
            'reason' => null,
        ];
    }

    /**
     * Process expired task completions based on settings
     * This should be called by a scheduled job (cron)
     *
     * @return array
     */
    public function processExpiredTaskCompletions(): array
    {
        // Check if feature is enabled
        if (!SystemSetting::isTaskApprovalExpiryEnabled()) {
            return [
                'success' => true,
                'message' => 'Task approval expiry is disabled',
                'processed' => 0,
            ];
        }

        $expiryHours = SystemSetting::getTaskApprovalExpiryInHours();
        $action = SystemSetting::getTaskApprovalExpiryAction();
        
        // Find pending completions that have exceeded the expiry time
        $expiredCompletions = TaskCompletion::pending()
            ->where('submitted_at', '<=', now()->subHours($expiryHours))
            ->get();

        $processed = 0;
        $autoApproved = 0;
        $expired = 0;

        foreach ($expiredCompletions as $completion) {
            try {
                DB::transaction(function () use ($completion, $action, &$processed, &$autoApproved, &$expired) {
                    if ($action === 'auto_approve') {
                        // Auto-approve and award earnings
                        $result = $this->awardTaskEarnings($completion);
                        if ($result['success']) {
                            $autoApproved++;
                            $processed++;
                        }
                    } else {
                        // Expire/reject the submission
                        $completion->status = TaskCompletion::STATUS_REJECTED;
                        $completion->rejection_reason = 'expired';
                        $completion->admin_notes = 'Automatically expired due to non-approval within the configured time limit';
                        $completion->reviewed_at = now();
                        $completion->save();
                        $expired++;
                        $processed++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error processing expired task completion', [
                    'completion_id' => $completion->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Processed expired task completions', [
            'total_processed' => $processed,
            'auto_approved' => $autoApproved,
            'expired' => $expired,
            'action' => $action,
        ]);

        return [
            'success' => true,
            'message' => "Processed {$processed} expired completions",
            'processed' => $processed,
            'auto_approved' => $autoApproved,
            'expired' => $expired,
            'action' => $action,
        ];
    }
}
