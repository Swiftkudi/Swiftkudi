<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AccountTypeReminder;
use App\Notifications\CompleteOnboardingReminder;
use App\Services\NotificationManager;
use Illuminate\Support\Facades\Log;

/**
 * Service to handle account type checking and notifications.
 */
class AccountTypeService
{
    /**
     * Notification manager instance.
     */
    protected NotificationManager $notificationManager;

    /**
     * Valid account types in the system.
     */
    public const VALID_ACCOUNT_TYPES = [
        'earner',
        'task_creator',
        'freelancer',
        'digital_seller',
        'growth_seller',
        'buyer',
        'admin',
    ];

    /**
     * Constructor.
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * Check if user already has an account type.
     */
    public function hasAccountType(User $user): bool
    {
        return !empty($user->account_type) && in_array($user->account_type, self::VALID_ACCOUNT_TYPES);
    }

    /**
     * Get the user's current account type.
     */
    public function getAccountType(User $user): ?string
    {
        return $user->account_type;
    }

    /**
     * Get human-readable account type label.
     */
    public function getAccountTypeLabel(?string $accountType): string
    {
        if (empty($accountType)) {
            return 'None';
        }

         return match ($accountType) {
             'earner' => 'Earner',
             'task_creator' => 'Task Creator',
             'freelancer' => 'Freelancer',
             'digital_seller' => 'Digital Product Seller',
             'growth_seller' => 'Growth Seller',
             'buyer' => 'Buyer',
             'admin' => 'Admin',
             default => ucfirst(str_replace('_', ' ', $accountType)),
         };
    }

    /**
     * Check if user has account type and redirect to appropriate location.
     * Also sends notification if user tries to access onboarding with existing account type.
     */
    public function checkAndRedirect(User $user, bool $sendNotification = true): array
    {
        if (!$this->hasAccountType($user)) {
            return [
                'has_account_type' => false,
                'redirect_needed' => true,
                'redirect_route' => 'onboarding.select-type',
                'account_type' => null,
                'account_type_label' => null,
            ];
        }

        // User already has account type
        $accountTypeLabel = $this->getAccountTypeLabel($user->account_type);

        // Send notification if requested
        if ($sendNotification) {
            $this->sendAccountTypeReminder($user);
        }

        return [
            'has_account_type' => true,
            'redirect_needed' => true,
            'redirect_route' => 'dashboard',
            'account_type' => $user->account_type,
            'account_type_label' => $accountTypeLabel,
            'message' => "You already have an account type: {$accountTypeLabel}",
        ];
    }

    /**
     * Send account type reminder notification to user.
     * Sends via email, in-app, and push notification channels.
     */
    public function sendAccountTypeReminder(User $user): void
    {
        try {
            $this->notificationManager->notify(
                NotificationManager::EVENT_ACCOUNT_TYPE_REMINDER,
                $user,
                [
                    'account_type' => $user->account_type,
                    'account_type_label' => $this->getAccountTypeLabel($user->account_type),
                ]
            );

            Log::info('Account type reminder sent', [
                'user_id' => $user->id,
                'account_type' => $user->account_type,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send account type reminder notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send onboarding reminder to users without an account type.
     * Encourages them to complete their account setup.
     */
    public function sendOnboardingReminder(User $user): void
    {
        if ($this->hasAccountType($user)) {
            // User already has account type, don't send reminder
            return;
        }

        try {
            $this->notificationManager->notify(
                NotificationManager::EVENT_ONBOARDING_REMINDER,
                $user,
                [
                    'action_url' => route('onboarding.select'),
                ]
            );

            Log::info('Onboarding reminder sent to user without account type', [
                'user_id' => $user->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send onboarding reminder notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send reminders to all users without an account type.
     * Useful for batch processing or cron job.
     */
    public function sendRemindersToUsersWithoutAccountType(): int
    {
        $users = User::whereNull('account_type')
            ->orWhere('account_type', '')
            ->where('is_suspended', false)
            ->whereNotNull('email_verified_at')
            ->get();

        $sentCount = 0;

        foreach ($users as $user) {
            $this->sendOnboardingReminder($user);
            $sentCount++;
        }

        Log::info("Sent onboarding reminders to {$sentCount} users without account type");

        return $sentCount;
    }

    /**
     * Check if user can change their account type (optional feature).
     * By default, users cannot change their account type once set.
     */
    public function canChangeAccountType(User $user): bool
    {
        // Default: once account type is set, it cannot be changed
        // Override this method to implement account type change logic
        return false;
    }

    /**
     * Get onboarding route based on account type.
     */
    public function getOnboardingRoute(?string $accountType): ?string
    {
        return match ($accountType) {
            'earner', 'task_creator' => 'start-your-journey',
            'freelancer' => 'onboarding.freelancer',
            'digital_seller' => 'onboarding.digital-product',
            'growth_seller' => 'onboarding.growth',
            'buyer' => 'onboarding.buyer-categories',
            default => null,
        };
    }

    /**
     * Get all available account types with labels.
     */
    public function getAvailableAccountTypes(): array
    {
        return [
            [
                'value' => 'earner',
                'label' => 'Earner',
                'description' => 'Complete tasks and earn money',
                'icon' => 'currency-naira',
            ],
            [
                'value' => 'task_creator',
                'label' => 'Task Creator',
                'description' => 'Post tasks and hire people',
                'icon' => 'clipboard-list',
            ],
            [
                'value' => 'freelancer',
                'label' => 'Freelancer',
                'description' => 'Offer professional services',
                'icon' => 'briefcase',
            ],
            [
                'value' => 'digital_seller',
                'label' => 'Digital Product Seller',
                'description' => 'Sell digital products online',
                'icon' => 'download',
            ],
            [
                'value' => 'growth_seller',
                'label' => 'Growth Seller',
                'description' => 'Offer growth services',
                'icon' => 'chart-growth',
            ],
            [
                'value' => 'buyer',
                'label' => 'Buyer',
                'description' => 'Browse and purchase services',
                'icon' => 'shopping-cart',
            ],
        ];
    }
}
