<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EarningsUnlocked extends Notification
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
        return (new MailMessage)
            ->subject('🎉 Your Earning Access Has Been Unlocked!')
            ->greeting("Congratulations, {$notifiable->name}!")
            ->line('Great news! You can now start completing tasks and earning money on SwiftKudi.')
            ->line('Your first campaign has been successfully created, unlocking your earning potential.')
            ->action('Start Earning', route('tasks.index'))
            ->line('Remember: The more quality tasks you create, the more visibility and earnings you unlock!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '🎉 Earnings Unlocked!',
            'message' => 'Your first campaign has been created. You can now start completing tasks and earning money!',
            'action_url' => route('tasks.index'),
            'type' => 'earnings_unlocked',
        ];
    }
}
