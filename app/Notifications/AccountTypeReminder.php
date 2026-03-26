<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent when an existing user tries to access onboarding
 * but already has an account type selected.
 */
class AccountTypeReminder extends Notification
{
    use Queueable;

    /**
     * The user's current account type.
     */
    protected string $accountType;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $accountTypeLabel = $this->getAccountTypeLabel($this->accountType);
        $dashboardUrl = route('dashboard');

        return (new MailMessage)
            ->subject("You Already Have an Account Type - {$accountTypeLabel}")
            ->greeting("Hi {$notifiable->name},")
            ->line("We noticed you tried to start the onboarding process again.")
            ->line("You already have an active account type: <strong>{$accountTypeLabel}</strong>")
            ->line("Your account is set up and ready. You can continue using SwiftKudi without needing to complete onboarding again.")
            ->action('Go to Dashboard', $dashboardUrl)
            ->line('If you believe this is an error or want to add another account type, please contact support.')
            ->line('Thank you for using SwiftKudi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'You Already Have an Account Type',
            'message' => "Your account type is already set to: {$this->getAccountTypeLabel($this->accountType)}. No action needed.",
            'action_url' => route('dashboard'),
            'type' => 'account_type_reminder',
            'account_type' => $this->accountType,
        ];
    }

    /**
     * Get the database (in-app) representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Convert account type to human-readable label.
     */
    protected function getAccountTypeLabel(string $accountType): string
    {
        return match ($accountType) {
            'earner' => 'Earner',
            'task_creator' => 'Task Creator',
            'freelancer' => 'Freelancer',
            'digital_seller' => 'Digital Product Seller',
            'growth_seller' => 'Growth Seller',
            'buyer' => 'Buyer',
            default => ucfirst(str_replace('_', ' ', $accountType)),
        };
    }
}
