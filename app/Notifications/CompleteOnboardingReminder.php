<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to existing users without an account type,
 * encouraging them to complete their onboarding.
 */
class CompleteOnboardingReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
        $onboardingUrl = route('onboarding.select');

        return (new MailMessage)
            ->subject('Complete Your SwiftKudi Setup - Choose Your Account Type')
            ->greeting("Hi {$notifiable->name},")
            ->line('Welcome to SwiftKudi! We noticed you haven\'t completed your account setup yet.')
            ->line('To get the most out of SwiftKudi, please choose your account type:')
            ->line('• <strong>Earner</strong> - Complete tasks and earn money')
            ->line('• <strong>Task Creator</strong> - Post tasks and hire people')
            ->line('• <strong>Freelancer</strong> - Offer professional services')
            ->line('• <strong>Digital Product Seller</strong> - Sell digital products')
            ->line('• <strong>Growth Seller</strong> - Offer growth services')
            ->line('• <strong>Buyer</strong> - Browse and purchase services')
            ->action('Complete Your Setup', $onboardingUrl)
            ->line('This only takes a minute and unlocks all the features SwiftKudi has to offer!')
            ->line('Thank you for choosing SwiftKudi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Complete Your Account Setup',
            'message' => 'Choose your account type to get started with SwiftKudi',
            'action_url' => route('onboarding.select'),
            'type' => 'complete_onboarding_reminder',
        ];
    }

    /**
     * Get the database (in-app) representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
