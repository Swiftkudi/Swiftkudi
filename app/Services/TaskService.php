<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\TaskNew;
use App\Models\TaskSubmissionNew;
use App\Models\TaskWalletTransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    protected $earnDeskService;
    protected $gateProgressService;

    public function __construct(SwiftKudiService $earnDeskService, TaskGateProgressService $gateProgressService)
    {
        $this->earnDeskService = $earnDeskService;
        $this->gateProgressService = $gateProgressService;
    }

    /**
     * Get system settings
     */
    public function getSettings(): array
    {
        return [
            'auto_approve_days' => (int) SystemSetting::get('task_auto_approve_days', 3),
            'min_reward' => (float) SystemSetting::get('task_min_reward', 10),
            'max_workers' => (int) SystemSetting::get('task_max_workers', 10000),
            'commission_rate' => (float) SystemSetting::get('task_commission_rate', 25),
        ];
    }

    /**
     * Validate task creation data
     */
    public function validateTask(array $data): array
    {
        $settings = $this->getSettings();
        $errors = [];

        // Validate reward
        if ($data['reward_per_user'] < $settings['min_reward']) {
            $errors[] = "Minimum reward per user is ₦{$settings['min_reward']}";
        }

        // Validate max workers
        if ($data['max_workers'] > $settings['max_workers']) {
            $errors[] = "Maximum workers cannot exceed {$settings['max_workers']}";
        }

        if ($data['max_workers'] < 1) {
            $errors[] = "At least 1 worker is required";
        }

        // Validate budget calculation
        $requiredBudget = $data['reward_per_user'] * $data['max_workers'];
        if ($requiredBudget < 100) {
            $errors[] = "Minimum total budget is ₦100";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'required_budget' => $requiredBudget,
        ];
    }

    /**
     * Create a new task (draft status)
     */
    public function createTask(User $user, array $data): array
    {
        try {
            // Validate
            $validation = $this->validateTask($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode('. ', $validation['errors']),
                ];
            }

            $task = TaskNew::create([
                'user_id' => $user->id,
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'proof_instructions' => $data['proof_instructions'] ?? null,
                'budget_total' => $validation['required_budget'],
                'reward_per_user' => $data['reward_per_user'],
                'max_workers' => $data['max_workers'],
                'status' => TaskNew::STATUS_DRAFT,
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            return [
                'success' => true,
                'task' => $task,
                'message' => 'Task created successfully',
                'required_funding' => $validation['required_budget'],
            ];
        } catch (\Exception $e) {
            Log::error('Task creation failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to create task: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fund a task - moves money from wallet to escrow
     */
    public function fundTask(TaskNew $task, User $user): array
    {
        try {
            return DB::transaction(function () use ($task, $user) {
                // Check task status
                if (!in_array($task->status, [TaskNew::STATUS_DRAFT, TaskNew::STATUS_PENDING_FUNDING])) {
                    return [
                        'success' => false,
                        'message' => 'Task cannot be funded in current status',
                    ];
                }

                $requiredAmount = $task->getRequiredFunding();
                $wallet = $user->wallet;

                if (!$wallet) {
                    return [
                        'success' => false,
                        'message' => 'Wallet not found',
                    ];
                }

                // Check balance
                $availableBalance = $wallet->withdrawable_balance + $wallet->promo_credit_balance;
                
                if ($availableBalance < $requiredAmount) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient funds to fund this task',
                        'required' => $requiredAmount,
                        'available' => $availableBalance,
                    ];
                }

                // Deduct from wallet
                $withdrawableUsed = min($requiredAmount, $wallet->withdrawable_balance);
                $promoUsed = $requiredAmount - $withdrawableUsed;

                if ($withdrawableUsed > 0) {
                    $wallet->decrement('withdrawable_balance', $withdrawableUsed);
                }
                if ($promoUsed > 0) {
                    $wallet->decrement('promo_credit_balance', $promoUsed);
                }

                // Record transaction
                $this->recordTransaction(
                    $task,
                    $user,
                    TaskWalletTransaction::TYPE_FUND,
                    $requiredAmount,
                    $wallet->withdrawable_balance + $wallet->promo_credit_balance,
                    "Task funding: {$task->title}"
                );

                // Update task escrow and status
                $task->update([
                    'escrow_balance' => DB::raw('escrow_balance + ' . $requiredAmount),
                    'status' => TaskNew::STATUS_ACTIVE,
                    'activated_at' => now(),
                ]);

                // Update user's task creation progress and check unlock status
                $this->gateProgressService->updateProgress($user, $requiredAmount);

                Log::info('Task funded', [
                    'task_id' => $task->id,
                    'user_id' => $user->id,
                    'amount' => $requiredAmount,
                ]);

                return [
                    'success' => true,
                    'message' => 'Task funded successfully and is now active!',
                    'funded_amount' => $requiredAmount,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Task funding failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to fund task: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Submit work for a task
     */
    public function submitWork(TaskNew $task, User $worker, array $proofData, ?string $notes = null): array
    {
        try {
            // Check task status
            if ($task->status !== TaskNew::STATUS_ACTIVE) {
                return [
                    'success' => false,
                    'message' => 'Task is not active',
                ];
            }

            // Check if task is full
            if ($task->isFull()) {
                return [
                    'success' => false,
                    'message' => 'Task is already full',
                ];
            }

            // Check if worker already submitted
            if ($task->hasWorkerSubmitted($worker->id)) {
                return [
                    'success' => false,
                    'message' => 'You have already submitted work for this task',
                ];
            }

            // Can't work on own task
            if ($task->user_id === $worker->id) {
                return [
                    'success' => false,
                    'message' => 'You cannot work on your own task',
                ];
            }

            // Create submission
            $submission = TaskSubmissionNew::create([
                'task_id' => $task->id,
                'worker_id' => $worker->id,
                'proof_data' => $proofData,
                'notes' => $notes,
                'status' => TaskSubmissionNew::STATUS_PENDING,
            ]);

            // Increment workers accepted count
            $task->increment('workers_accepted_count');

            // Check if task is now full
            if ($task->isFull()) {
                $task->update(['status' => TaskNew::STATUS_ACTIVE]); // Keep active but full
            }

            return [
                'success' => true,
                'message' => 'Work submitted successfully!',
                'submission' => $submission,
            ];
        } catch (\Exception $e) {
            Log::error('Task submission failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to submit work: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Approve a submission and pay worker
     */
    public function approveSubmission(TaskSubmissionNew $submission, User $approver): array
    {
        try {
            return DB::transaction(function () use ($submission, $approver) {
                $task = $submission->task;

                // Verify ownership
                if ($task->user_id !== $approver->id) {
                    return [
                        'success' => false,
                        'message' => 'Only task owner can approve submissions',
                    ];
                }

                // Check status
                if ($submission->status !== TaskSubmissionNew::STATUS_PENDING) {
                    return [
                        'success' => false,
                        'message' => 'Submission already processed',
                    ];
                }

                // Check escrow balance
                if ($task->escrow_balance < $task->reward_per_user) {
                    return [
                        'success' => false,
                        'message' => 'Insufficient escrow balance for payout',
                    ];
                }

                $settings = $this->getSettings();
                $reward = $task->reward_per_user;
                $commission = $reward * ($settings['commission_rate'] / 100);
                $payout = $reward - $commission;

                // Get worker wallet
                $workerWallet = $submission->worker->wallet;
                
                if (!$workerWallet) {
                    return [
                        'success' => false,
                        'message' => 'Worker wallet not found',
                    ];
                }

                // Deduct from escrow
                $task->decrement('escrow_balance', $reward);
                $task->increment('total_paid', $payout);
                $task->increment('commission_earned', $commission);

                // Add to worker balance
                $workerWallet->addWithdrawable($payout, 'task_completion');

                // Mark submission as approved
                $submission->markApproved();

                // Increment completed count
                $task->increment('workers_completed_count');

                // Record transactions
                $this->recordTransaction(
                    $task,
                    $submission->worker,
                    TaskWalletTransaction::TYPE_PAYOUT,
                    $payout,
                    $workerWallet->withdrawable_balance,
                    "Task completion: {$task->title}"
                );

                $this->recordTransaction(
                    $task,
                    $task->creator,
                    TaskWalletTransaction::TYPE_COMMISSION,
                    $commission,
                    $task->creator->wallet->withdrawable_balance ?? 0,
                    "Commission: {$task->title}"
                );

                // Check if task is complete
                if ($task->shouldAutoComplete()) {
                    $task->markCompleted();
                }

                return [
                    'success' => true,
                    'message' => 'Submission approved! Worker paid ₦' . number_format($payout, 2),
                    'paid_amount' => $payout,
                    'commission' => $commission,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Approval failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to approve: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Reject a submission
     */
    public function rejectSubmission(TaskSubmissionNew $submission, User $rejector, string $reason): array
    {
        try {
            $task = $submission->task;

            // Verify ownership
            if ($task->user_id !== $rejector->id) {
                return [
                    'success' => false,
                    'message' => 'Only task owner can reject submissions',
                ];
            }

            // Check status
            if ($submission->status !== TaskSubmissionNew::STATUS_PENDING) {
                return [
                    'success' => false,
                    'message' => 'Submission already processed',
                ];
            }

            // Mark as rejected
            $submission->markRejected($reason);

            return [
                'success' => true,
                'message' => 'Submission rejected',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a task and refund remaining escrow
     */
    public function cancelTask(TaskNew $task, User $user): array
    {
        try {
            return DB::transaction(function () use ($task, $user) {
                // Verify ownership
                if ($task->user_id !== $user->id) {
                    return [
                        'success' => false,
                        'message' => 'Only task owner can cancel',
                    ];
                }

                // Check status
                if (!in_array($task->status, [TaskNew::STATUS_DRAFT, TaskNew::STATUS_PENDING_FUNDING, TaskNew::STATUS_ACTIVE, TaskNew::STATUS_PAUSED])) {
                    return [
                        'success' => false,
                        'message' => 'Task cannot be cancelled in current status',
                    ];
                }

                $refundAmount = $task->escrow_balance;

                if ($refundAmount > 0) {
                    $wallet = $user->wallet;
                    
                    // Refund to wallet
                    $wallet->addWithdrawable($refundAmount, 'task_cancellation');

                    // Record transaction
                    $this->recordTransaction(
                        $task,
                        $user,
                        TaskWalletTransaction::TYPE_REFUND,
                        $refundAmount,
                        $wallet->withdrawable_balance,
                        "Task cancellation refund: {$task->title}"
                    );
                }

                // Update task status
                $task->update([
                    'status' => TaskNew::STATUS_CANCELLED,
                    'escrow_balance' => 0,
                ]);

                return [
                    'success' => true,
                    'message' => 'Task cancelled. Refund: ₦' . number_format($refundAmount, 2),
                    'refund_amount' => $refundAmount,
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to cancel: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Pause an active task
     */
    public function pauseTask(TaskNew $task, User $user): array
    {
        try {
            return DB::transaction(function () use ($task, $user) {
                // Verify ownership
                if ($task->user_id !== $user->id) {
                    return [
                        'success' => false,
                        'message' => 'Only task owner can pause',
                    ];
                }

                // Can only pause active tasks
                if ($task->status !== TaskNew::STATUS_ACTIVE) {
                    return [
                        'success' => false,
                        'message' => 'Only active tasks can be paused',
                    ];
                }

                $task->update([
                    'status' => TaskNew::STATUS_PAUSED,
                ]);

                return [
                    'success' => true,
                    'message' => 'Task paused successfully',
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to pause task: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Resume a paused task
     */
    public function resumeTask(TaskNew $task, User $user): array
    {
        try {
            return DB::transaction(function () use ($task, $user) {
                // Verify ownership
                if ($task->user_id !== $user->id) {
                    return [
                        'success' => false,
                        'message' => 'Only task owner can resume',
                    ];
                }

                // Can only resume paused tasks
                if ($task->status !== TaskNew::STATUS_PAUSED) {
                    return [
                        'success' => false,
                        'message' => 'Only paused tasks can be resumed',
                    ];
                }

                $task->update([
                    'status' => TaskNew::STATUS_ACTIVE,
                ]);

                return [
                    'success' => true,
                    'message' => 'Task resumed successfully',
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to resume task: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Record a wallet transaction
     */
    protected function recordTransaction(TaskNew $task, User $user, string $type, float $amount, float $balanceAfter, string $description): void
    {
        TaskWalletTransaction::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceAfter - $amount, // Approximate
            'balance_after' => $balanceAfter,
            'description' => $description,
        ]);
    }
}
