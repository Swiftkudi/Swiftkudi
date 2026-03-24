<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivationReminder extends Notification
{
    use Queueable;

    /**
     * Reminder type: first, second, third
     */
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type = 'first')
    {
        $this->type = $type;
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
        $subject = $this->getSubject();
        $content = $this->getContent();

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hi {$notifiable->name},")
            ->line($content['intro'])
            ->line($content['main']);

        // Add items if present
        if (isset($content['main_items'])) {
            foreach ($content['main_items'] as $item) {
                $mail->line($item);
            }
        }

        // Add stats if present
        if (isset($content['main_stats'])) {
            foreach ($content['main_stats'] as $stat) {
                $mail->line($stat);
            }
        }

        // Add CTA if present
        if (!empty($content['cta_url'])) {
            $mail->action($content['cta_text'] ?? 'Activate Now', url($content['cta_url']));
        }

        return $mail->line($content['outro']);
    }

    /**
     * Get email subject based on reminder type.
     */
    protected function getSubject(): string
    {
        switch ($this->type) {
            case 'first':
                return 'Complete Your Registration - Tasks Await! 🎯';
            case 'second':
                return 'High-Paying Tasks Available Now! 💰';
            case 'third':
                return 'Join Thousands Earning on SwiftKudi - Last Chance! 🚀';
            default:
                return 'Reminder: Complete Your Registration';
        }
    }

    /**
     * Get email content based on reminder type.
     */
    protected function getContent(): array
    {
        $activationFee = number_format(\App\Services\SwiftKudiService::ACTIVATION_FEE);

        switch ($this->type) {
            case 'first':
                return [
                    'intro' => 'You registered on SwiftKudi but haven\'t completed your activation yet.',
                    'main' => "Great tasks are waiting for you! Activate now to start completing tasks and earning real money. Your ₦{$activationFee} activation fee gives you full access to the platform.",
                    'cta_url' => route('wallet.activate'),
                    'cta_text' => 'Activate Now',
                    'outro' => 'Don\'t miss out on your earning opportunities!',
                ];
            case 'second':
                return [
                    'intro' => 'We noticed you haven\'t activated your account yet.',
                    'main' => 'High-paying tasks are now available! Users are earning ₦2,500 - ₦5,000+ on tasks like:',
                    'main_items' => [
                        '• Micro tasks (likes, comments, follows)',
                        '• UGC content creation',
                        '• Social media growth campaigns',
                    ],
                    'cta_url' => route('wallet.activate'),
                    'cta_text' => 'Activate & Start Earning',
                    'outro' => 'Your earning potential awaits!',
                ];
            case 'third':
                return [
                    'intro' => 'Last reminder before you miss out!',
                    'main' => 'Join thousands of active earners on SwiftKudi. Here\'s what our users are earning:',
                    'main_stats' => [
                        '• Top earners: ₦50,000+ per month',
                        '• Average user: ₦15,000 - ₦25,000/month',
                        '• New earners: ₦5,000 - ₦10,000 in first week',
                    ],
                    'cta_url' => route('wallet.activate'),
                    'cta_text' => 'Activate Now & Start Earning',
                    'outro' => 'This is your last reminder. Don\'t let your earning opportunities pass!',
                ];
            default:
                return [
                    'intro' => 'You registered on SwiftKudi but haven\'t completed your activation yet.',
                    'main' => 'Activate your account to start earning from tasks.',
                    'cta_url' => route('wallet.activate'),
                    'cta_text' => 'Activate Now',
                    'outro' => 'Thank you for choosing SwiftKudi!',
                ];
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $subject = $this->getSubject();
        $content = $this->getContent();

        return [
            'title' => $subject,
            'message' => $content['main'],
            'action_url' => $content['cta_url'] ?? null,
            'type' => 'activation_reminder',
            'reminder_type' => $this->type,
        ];
    }
}
