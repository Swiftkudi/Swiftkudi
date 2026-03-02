<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\EarningsUnlocked;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for managing user task creation gate progress.
 * Updates budget tracking and unlock status after successful task creation.
 */
class TaskGateProgressService
{
    /**
     * Update user's task creation budget and check unlock status.
     * Call this immediately after a task is successfully created.
     *
     * @param User $user
     * @param float $taskBudget The budget of the newly created task
     * @return array ['unlocked' => bool, 'previous_budget' => float, 'new_budget' => float]
     */
    public function updateProgress(User $user, float $taskBudget): array
    {
        $previousBudget = $user->total_created_task_budget ?? 0;
        $newBudget = $previousBudget + $taskBudget;

        // Update budget atomically
        DB::transaction(function () use ($user, $taskBudget, $newBudget) {
            $user->increment('total_created_task_budget', $taskBudget);
            $user->refresh();

            // Check if user just reached unlock threshold
            if (!$user->has_completed_mandatory_creation) {
                $minimumBudget = SystemSetting::get('minimum_required_budget', 2500);

                if ($newBudget >= $minimumBudget) {
                    // User has reached the threshold - unlock earnings
                    $user->has_completed_mandatory_creation = true;
                    $user->task_creation_unlocked_at = now();
                    $user->save();

                    // Send unlock notification
                    try {
                        $user->notify(new EarningsUnlocked());
                    } catch (\Exception $e) {
                        Log::warning('Failed to send earnings unlocked notification', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    Log::info('User unlocked earnings access', [
                        'user_id' => $user->id,
                        'total_budget' => $newBudget,
                        'threshold' => $minimumBudget,
                    ]);
                }
            }
        });

        return [
            'unlocked' => $user->has_completed_mandatory_creation ?? false,
            'previous_budget' => $previousBudget,
            'new_budget' => $newBudget,
        ];
    }

    /**
     * Check if user has unlocked earnings access.
     *
     * @param User $user
     * @return bool
     */
    public function isUnlocked(User $user): bool
    {
        // Admins are always unlocked
        if ($user->is_admin) {
            return true;
        }

        // Check if gate is enabled
        $gateEnabled = SystemSetting::get('mandatory_task_creation_enabled', true);
        if (!$gateEnabled) {
            return true;
        }

        // Check unlock status
        return $user->has_completed_mandatory_creation ?? false;
    }

    /**
     * Get user's progress toward unlock threshold.
     *
     * @param User $user
     * @return array ['current' => float, 'required' => float, 'remaining' => float, 'percentage' => float, 'unlocked' => bool]
     */
    public function getProgress(User $user): array
    {
        $current = $user->total_created_task_budget ?? 0;
        $required = SystemSetting::get('minimum_required_budget', 2500);
        $remaining = max(0, $required - $current);
        $percentage = $required > 0 ? min(100, ($current / $required) * 100) : 100;

        return [
            'current' => $current,
            'required' => $required,
            'remaining' => $remaining,
            'percentage' => round($percentage, 1),
            'unlocked' => $this->isUnlocked($user),
        ];
    }
}
