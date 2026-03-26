<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
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

        // Update budget atomically using locking to prevent race conditions
        DB::transaction(function () use ($user, $taskBudget, $newBudget, &$previousBudget) {
            // Lock the user row for update to prevent race conditions
            $lockedUser = DB::table('users')
                ->where('id', $user->id)
                ->lockForUpdate()
                ->first();

            $currentBudget = $lockedUser->total_created_task_budget ?? 0;
            $previousBudget = $currentBudget;
            $updatedBudget = $currentBudget + $taskBudget;

            // Update the budget
            DB::table('users')
                ->where('id', $user->id)
                ->update(['total_created_task_budget' => $updatedBudget]);

            // Check unlock progress for task creators first task + earners threshold
            if (!$lockedUser->has_completed_mandatory_creation) {
                $shouldUnlock = false;

                if ($lockedUser->account_type === 'task_creator') {
                    // Task creators unlock after first task creation
                    $shouldUnlock = true;
                } else {
                    $minimumBudget = SystemSetting::get('minimum_required_budget', 2500);
                    if ($updatedBudget >= $minimumBudget) {
                        $shouldUnlock = true;
                    }
                }

                if ($shouldUnlock) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'has_completed_mandatory_creation' => true,
                            'task_creation_unlocked_at' => now(),
                        ]);

                    // Send unlock notification
                    try {
                        app(\App\Services\NotificationDispatchService::class)->sendToUser(
                            $user,
                            'Earnings Unlocked',
                            'Congratulations! You have unlocked earnings access after meeting the required task creation budget.',
                            \App\Models\Notification::TYPE_SYSTEM,
                            ['action_url' => route('start-journey.unlock-success')]
                        );
                    } catch (\Exception $e) {
                        Log::warning('Failed to send earnings unlocked notification', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    Log::info('User unlocked earnings access', [
                        'user_id' => $user->id,
                        'total_budget' => $updatedBudget,
                        'threshold' => $minimumBudget,
                    ]);
                }
            }
        });

        // Refresh user to get updated state
        $user->refresh();

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
        if (!SystemSetting::isMandatoryTaskCreationEnabled()) {
            return [
                'current' => $user->total_created_task_budget ?? 0,
                'required' => 0,
                'remaining' => 0,
                'percentage' => 100.0,
                'unlocked' => true,
            ];
        }

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

    /**
     * Unlock a marketplace seller (freelancer, digital_seller, growth_seller) after they create their first item.
     * Call this after a freelancer creates a service, digital seller uploads a product, or growth seller creates a listing.
     *
     * @param User $user
     * @param string $sellerType 'freelancer', 'digital_seller', or 'growth_seller'
     * @return array ['success' => bool, 'message' => string]
     */
    public function unlockMarketplaceSeller(User $user, string $sellerType): array
    {
        $updates = [];
        $fieldToCheck = '';
        $fieldToSet = '';

        switch ($sellerType) {
            case 'freelancer':
                $fieldToCheck = 'freelancer_service_created';
                $fieldToSet = 'freelancer_service_created';
                break;
            case 'digital_seller':
                $fieldToCheck = 'digital_product_uploaded';
                $fieldToSet = 'digital_product_uploaded';
                break;
            case 'growth_seller':
                $fieldToCheck = 'growth_listing_created';
                $fieldToSet = 'growth_listing_created';
                break;
            default:
                return ['success' => false, 'message' => 'Invalid seller type'];
        }

        // Only set the specific field, don't override other fields
        $updates[$fieldToSet] = true;

        // Only set has_completed_mandatory_creation if not already set
        if (!$user->has_completed_mandatory_creation) {
            $updates['has_completed_mandatory_creation'] = true;
            $updates['task_creation_unlocked_at'] = now();
        }

        $user->update($updates);

        Log::info('Marketplace seller unlocked', [
            'user_id' => $user->id,
            'seller_type' => $sellerType,
            'field_set' => $fieldToSet,
        ]);

        return ['success' => true, 'message' => ucfirst($sellerType) . ' marketplace access unlocked'];
    }
}
